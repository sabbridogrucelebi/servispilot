<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Fleet\Driver;
use App\Models\User;
use Carbon\Carbon;

class NotifyBirthdays extends Command
{
    protected $signature = 'notify:birthdays';
    protected $description = 'Send push notifications for personnel birthdays today';

    public function handle()
    {
        $today = Carbon::now();
        
        $drivers = Driver::whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->where('is_active', true)
            ->get();

        if ($drivers->isEmpty()) {
            $this->info('Bugün doğum günü olan personel bulunamadı.');
            return;
        }

        $companyDrivers = $drivers->groupBy('company_id');
        $totalSent = 0;

        foreach ($companyDrivers as $companyId => $driversInCompany) {
            $users = User::where('company_id', $companyId)
                ->whereNotNull('expo_push_token')
                ->get();

            if ($users->isEmpty()) {
                continue;
            }

            $driverNames = $driversInCompany->pluck('full_name')->join(', ');
            $title = '🎉 Bugün Doğum Günü!';
            $body = "Bugün {$driverNames} adlı personellerinizin doğum günü. Onları tebrik etmeyi unutmayın!";

            $messages = [];
            foreach ($users as $user) {
                if (!str_starts_with($user->expo_push_token, 'ExponentPushToken') && !str_starts_with($user->expo_push_token, 'ExpoPushToken')) {
                    continue;
                }
                $messages[] = [
                    'to' => $user->expo_push_token,
                    'sound' => 'default',
                    'title' => $title,
                    'body' => $body,
                    'data' => ['type' => 'birthday'],
                ];
            }

            if (!empty($messages)) {
                $response = Http::post('https://exp.host/--/api/v2/push/send', $messages);
                if ($response->successful()) {
                    $totalSent += count($messages);
                } else {
                    $this->error("Company {$companyId} için push bildirimi gönderilemedi: " . $response->body());
                }
            }
        }

        $this->info("Toplam {$totalSent} adet doğum günü bildirimi gönderildi.");
    }
}
