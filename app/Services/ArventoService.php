<?php

namespace App\Services;

use App\Models\VehicleTrackingSetting;
use Illuminate\Support\Facades\Http;
use Exception;

class ArventoService
{
    protected $url = "http://ws.arvento.com/v1/report.asmx";
    protected $setting;

    public function __construct(VehicleTrackingSetting $setting)
    {
        $this->setting = $setting;
    }

    /**
     * Arvento Web Servisine manuel SOAP isteği gönderir.
     */
    protected function call($method, $params = [])
    {
        try {
            // Güvenlik: Method adı whitelist dışı olamaz ve XML güvenli olmalı
            $safeMethod = preg_replace('/[^A-Za-z0-9]/', '', (string) $method);

            $xmlParams = "";
            foreach ($params as $key => $value) {
                // XML Injection, CRLF Injection ve özel karakter kaçırma
                $safeKey = preg_replace('/[^A-Za-z0-9_]/', '', (string) $key);
                $safeValue = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xmlParams .= "<{$safeKey}>{$safeValue}</{$safeKey}>";
            }

            $envelope = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <' . $safeMethod . ' xmlns="http://www.arvento.com/">
      ' . $xmlParams . '
    </' . $safeMethod . '>
  </soap:Body>
</soap:Envelope>';

            $response = Http::timeout(15)->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction'   => 'http://www.arvento.com/' . $safeMethod,
            ])->withBody($envelope, 'text/xml')->post($this->url);

            if ($response->successful()) {
                $body = $response->body();

                $resultNode = $safeMethod . "Result";
                if (preg_match('/<' . $resultNode . '[^>]*>(.*?)<\/' . $resultNode . '>/s', $body, $matches)) {
                    return html_entity_decode($matches[1]);
                }
                return null;
            }

            return null;
        } catch (Exception $e) {
            \Log::error("Arvento API Error ({$safeMethod}): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tüm araçların anlık konum bilgilerini (JSON formatında) çeker.
     * Yetki listesindeki: GetVehicleStatusJSON
     */
    public function getVehicleStatus()
    {
        $result = $this->call('GetVehicleStatusJSON', [
            'Username' => $this->setting->username,
            'PIN1'     => $this->setting->app_id ?? '',
            'PIN2'     => $this->setting->app_key ?? '',
            'callback' => '',
        ]);

        if (empty($result)) return [];

        try {
            $data = json_decode($result, true);
            if (!isset($data['GetVehicleStatusJSON'])) return [];

            $vehicles = [];
            foreach ($data['GetVehicleStatusJSON'] as $v) {
                $vehicles[] = [
                    'Node'         => (string)($v['Node'] ?? ''),
                    'LicensePlate' => (string)($v['LicensePlate'] ?? $v['Node'] ?? 'Plakasız'),
                    'Latitude'     => (float)($v['Latitude'] ?? 0),
                    'Longitude'    => (float)($v['Longitude'] ?? 0),
                    'Speed'        => (int)($v['Speed'] ?? 0),
                    'Address'      => (string)($v['Address'] ?? ''),
                    'Course'       => (int)($v['Course'] ?? 0),
                ];
            }
            return $vehicles;
        } catch (Exception $e) {
            \Log::error("Arvento JSON Parse Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Belirli bir aracın bugün yaptığı maksimum hızı bulur.
     * Yetki listesindeki: SpeedReport
     */
    public function getVehicleDailyStats($licensePlate)
    {
        $vehicles = $this->getVehicleStatus();
        $targetVehicle = collect($vehicles)->filter(function($v) use ($licensePlate) {
            return ($v['LicensePlate'] == $licensePlate) || ($v['Node'] == $licensePlate);
        })->first();
        
        if (!$targetVehicle) return null;

        $nodeId = $targetVehicle['Node'];
        $todayStart = now()->startOfDay()->format('d.m.Y H:i:s');
        $todayEnd = now()->format('d.m.Y H:i:s');

        // SpeedReport metodunu kullanıyoruz
        $result = $this->call('SpeedReport', [
            'Username'  => $this->setting->username,
            'PIN1'      => $this->setting->app_id ?? '',
            'PIN2'      => $this->setting->app_key ?? '',
            'StartDate' => $todayStart,
            'EndDate'   => $todayEnd,
            'Node'      => $nodeId,
            'Group'     => '',
            'SpeedLimit' => '0',
            'Compress'  => '0',
            'Language'  => '0',
        ]);

        if (!$result) return null;

        try {
            // SpeedReport sonucunu ayıkla (XML DataSet döner)
            $wrapped = "<root>".$result."</root>";
            $xml = simplexml_load_string($wrapped);
            $rows = $xml->xpath('//Table');
            
            $maxSpeed = 0;
            foreach ($rows as $row) {
                $speed = (int)$row->Hız;
                if ($speed > $maxSpeed) $maxSpeed = $speed;
            }

            return [
                'max_speed' => $maxSpeed,
                'distance' => 0, // SpeedReport mesafe dönmeyebilir
            ];
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Plaka - Cihaz No eşleşmelerini çeker.
     * Yetki listesindeki: GetLicensePlateNodeMappings
     */
    public function getLicensePlateNodeMappings()
    {
        return $this->call('GetLicensePlateNodeMappings', [
            'Username' => $this->setting->username,
            'PIN1'     => $this->setting->app_id ?? '',
            'PIN2'     => $this->setting->app_key ?? '',
        ]);
    }
}
