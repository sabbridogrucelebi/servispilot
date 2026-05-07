<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupTenantData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-tenant-data';
    protected $description = 'Her şirketin verisini günlük olarak yedekler ve 7 gün tutar';

    public function handle()
    {
        $companies = \App\Models\Company::all();
        $date = now()->format('Y-m-d');
        
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

        foreach ($companies as $company) {
            $baseDir = storage_path("app/YEDEKLEMELER");
            if (!file_exists($baseDir)) {
                mkdir($baseDir, 0755, true);
            }

            $folderName = "firma_{$company->id}_" . \Illuminate\Support\Str::slug($company->name);
            $backupDir = $baseDir . '/' . $folderName;

            // Eğer firmanın adı değişmişse, eski klasörü bul ve ismini güncelle
            $existingDirs = glob($baseDir . "/firma_{$company->id}_*");
            if (!empty($existingDirs) && $existingDirs[0] !== $backupDir) {
                rename($existingDirs[0], $backupDir);
            } elseif (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Eski yedekleri sil (7 günden eski)
            $files = \Illuminate\Support\Facades\File::files($backupDir);
            foreach ($files as $file) {
                if (now()->diffInDays(\Carbon\Carbon::createFromTimestamp($file->getMTime())) > 7) {
                    \Illuminate\Support\Facades\File::delete($file);
                }
            }

            // Yeni yedeği oluştur
            $zip = new \ZipArchive;
            $zipPath = $backupDir . "/yedek_{$date}.zip";
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($models as $name => $modelClass) {
                    if (class_exists($modelClass)) {
                        $data = $modelClass::withoutGlobalScopes()->where('company_id', $company->id)->get()->toJson();
                        $zip->addFromString("{$name}.json", $data);
                    }
                }
                $zip->close();
            }
        }
        
        $this->info("Yedekleme başarıyla tamamlandı.");
    }
}
