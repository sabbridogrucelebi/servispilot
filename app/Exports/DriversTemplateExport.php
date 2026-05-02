<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DriversTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'ad_soyad',
            'tc_kimlik_no',
            'telefon',
            'eposta',
            'dogum_tarihi',
            'ise_giris_tarihi',
            'bagli_arac_plaka',
            'maas',
            'ehliyet_sinifi',
            'src_turu',
            'adres',
            'notlar'
        ];
    }

    public function array(): array
    {
        return [];
    }
}
