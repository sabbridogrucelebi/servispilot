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
    protected $signature = 'app:backup-tenant-data {--company_id= : Belirli bir firmayı yedekle} {--superadmin : Sadece superadmin yedeğini al}';
    protected $description = 'Her şirketin verisini günlük olarak yedekler ve 10 gün tutar';

    public function handle()
    {
        $companyId = $this->option('company_id');
        $onlySuperadmin = $this->option('superadmin');
        
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

        if ($onlySuperadmin) {
            $this->backupSuperAdmin($date);
            $this->info("Süper admin yedeği başarıyla tamamlandı.");
            return;
        }

        if ($companyId) {
            $companies = \App\Models\Company::where('id', $companyId)->get();
        } else {
            $companies = \App\Models\Company::all();
            // Tüm firmalar yedeklenirken süper admini de yedekle
            $this->backupSuperAdmin($date);
        }

        foreach ($companies as $company) {
            $this->backupCompany($company, $models, $date);
        }
        
        $this->info("Yedekleme başarıyla tamamlandı.");
    }

    protected function backupCompany($company, $models, $date)
    {
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

        // Modül bazlı klasör yapısı (kullanıcı talebi üzerine)
        foreach ($models as $name => $modelClass) {
            $moduleDir = $backupDir . '/' . $name;
            if (!file_exists($moduleDir)) {
                mkdir($moduleDir, 0755, true);
            }

            // Eski yedekleri sil (10 günden eski)
            $files = \Illuminate\Support\Facades\File::files($moduleDir);
            foreach ($files as $file) {
                if (now()->diffInDays(\Carbon\Carbon::createFromTimestamp($file->getMTime())) >= 10) {
                    \Illuminate\Support\Facades\File::delete($file);
                }
            }

            // Yeni yedeği ZIP olarak oluştur (modül başına)
            $zip = new \ZipArchive;
            $zipPath = $moduleDir . "/yedek_{$date}.zip";
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                if (class_exists($modelClass)) {
                    $data = $modelClass::withoutGlobalScopes()->where('company_id', $company->id)->get()->toJson();
                    $zip->addFromString("{$name}.json", $data);
                }
                $zip->close();
            }
        }
        
        $statusFile = storage_path("app/YEDEKLEMELER/.last_backup_{$company->id}");
        \Illuminate\Support\Facades\File::put($statusFile, now()->toIso8601String());
    }

    protected function backupSuperAdmin($date)
    {
        $baseDir = storage_path("app/YEDEKLEMELER/superadmin");
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $models = [
            'firmalar' => \App\Models\Company::class,
            'kullanicilar' => \App\Models\User::class, // company_id null olanlar
            'planlar' => \App\Models\Plan::class,
            'abonelikler' => \App\Models\Subscription::class,
        ];

        foreach ($models as $name => $modelClass) {
            $moduleDir = $baseDir . '/' . $name;
            if (!file_exists($moduleDir)) {
                mkdir($moduleDir, 0755, true);
            }

            // Eski yedekleri sil (10 günden eski)
            $files = \Illuminate\Support\Facades\File::files($moduleDir);
            foreach ($files as $file) {
                if (now()->diffInDays(\Carbon\Carbon::createFromTimestamp($file->getMTime())) >= 10) {
                    \Illuminate\Support\Facades\File::delete($file);
                }
            }

            // Yeni yedeği oluştur
            $zip = new \ZipArchive;
            $zipPath = $moduleDir . "/yedek_{$date}.zip";
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                if (class_exists($modelClass)) {
                    $query = $modelClass::withoutGlobalScopes();
                    if ($name === 'kullanicilar') {
                        $query->whereNull('company_id');
                    }
                    $data = $query->get()->toJson();
                    $zip->addFromString("{$name}.json", $data);
                }
                $zip->close();
            }
        }
        
        $statusFile = storage_path("app/YEDEKLEMELER/.last_backup_superadmin");
        \Illuminate\Support\Facades\File::put($statusFile, now()->toIso8601String());
    }
}
