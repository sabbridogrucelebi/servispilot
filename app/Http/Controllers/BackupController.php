<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class BackupController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $baseDir = storage_path("app/YEDEKLEMELER");
        $dirs = glob($baseDir . "/firma_{$companyId}_*");
        $backupDir = !empty($dirs) ? $dirs[0] : null;
        
        $backups = [];
        if ($backupDir && File::exists($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                if ($file->getExtension() === 'zip') {
                    $backups[] = [
                        'name' => $file->getFilename(),
                        'size' => round($file->getSize() / 1024, 2) . ' KB',
                        'date' => Carbon::createFromTimestamp($file->getMTime())->format('Y-m-d H:i:s'),
                        'path' => $file->getFilename()
                    ];
                }
            }
        }
        
        // Sort newest first
        usort($backups, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
        
        return view('backups.index', compact('backups'));
    }

    public function download($file)
    {
        $companyId = auth()->user()->company_id;
        $baseDir = storage_path("app/YEDEKLEMELER");
        $dirs = glob($baseDir . "/firma_{$companyId}_*");
        $backupDir = !empty($dirs) ? $dirs[0] : null;
        
        $path = $backupDir ? $backupDir . '/' . $file : null;
        
        if (!$path || !File::exists($path)) {
            return redirect()->back()->with('error', 'Yedek dosyası bulunamadı.');
        }
        
        return response()->download($path);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file' => 'required|string',
            'module' => 'required|string'
        ]);

        $companyId = auth()->user()->company_id;
        $file = $request->file;
        $module = $request->module; // 'yakitlar', 'araclar', vs.
        
        $baseDir = storage_path("app/YEDEKLEMELER");
        $dirs = glob($baseDir . "/firma_{$companyId}_*");
        $backupDir = !empty($dirs) ? $dirs[0] : null;
        
        $zipPath = $backupDir ? $backupDir . '/' . $file : null;
        
        if (!$zipPath || !File::exists($zipPath)) {
            return redirect()->back()->with('error', 'Yedek dosyası bulunamadı.');
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $jsonContent = $zip->getFromName("{$module}.json");
            $zip->close();

            if ($jsonContent === false) {
                return redirect()->back()->with('error', "Seçilen yedekte {$module} verisi bulunamadı.");
            }

            $data = json_decode($jsonContent, true);
            
            $modelClass = $this->getModelClass($module);
            
            if (!$modelClass) {
                return redirect()->back()->with('error', "Geçersiz modül.");
            }

            // Restore logic - upsert all rows
            \DB::beginTransaction();
            try {
                $modelClass::unguard();
                foreach ($data as $row) {
                    $modelClass::updateOrCreate(['id' => $row['id']], $row);
                }
                $modelClass::reguard();
                \DB::commit();
                return redirect()->back()->with('success', "{$module} yedeği başarıyla yüklendi ve veriler geri getirildi.");
            } catch (\Exception $e) {
                \DB::rollBack();
                $modelClass::reguard();
                return redirect()->back()->with('error', "Geri yükleme sırasında hata oluştu: " . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'ZIP dosyası açılamadı.');
    }

    private function getModelClass($module)
    {
        $models = [
            'araclar' => \App\Models\Fleet\Vehicle::class,
            'personeller' => \App\Models\Fleet\Driver::class,
            'musteriler' => \App\Models\Customer::class,
            'seferler' => \App\Models\Trip::class,
            'yakitlar' => \App\Models\Fuel::class,
            'bakimlar' => \App\Models\VehicleMaintenance::class,
            'cezalar' => \App\Models\TrafficPenalty::class,
            'maaslar' => \App\Models\Payroll::class,
        ];

        return $models[$module] ?? null;
    }
}
