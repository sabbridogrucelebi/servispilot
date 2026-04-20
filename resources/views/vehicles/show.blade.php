@extends('layouts.app')

@section('title', 'Araç Detayı')
@section('subtitle', 'Araç yönetim merkezi')

@section('content')

@php
    use Illuminate\Support\Collection;

    $activeTab = request('tab', 'general');

    $displayColor = $vehicle->color === 'Diğer'
        ? ($vehicle->other_color ?: 'Diğer')
        : ($vehicle->color ?: '-');

    $profitColorClass = $profit >= 0
        ? 'from-emerald-500 to-teal-500'
        : 'from-rose-500 to-pink-500';

    $profitTextClass = $profit >= 0
        ? 'text-emerald-600'
        : 'text-rose-600';

    $activeVehicleDocuments = collect($activeVehicleDocuments ?? []);
    $archivedVehicleDocuments = collect($archivedVehicleDocuments ?? []);
    $recentFuels = collect($recentFuels ?? []);
    $assignedDrivers = collect($assignedDrivers ?? []);
    $recentTrips = collect($recentTrips ?? []);
    $vehicleMaintenances = collect($vehicleMaintenances ?? []);
    $vehiclePenalties = collect($vehiclePenalties ?? []);
    $vehicleImages = collect($vehicleImages ?? []);

    $featuredImage = $vehicleImages->firstWhere('is_featured', true) ?? $vehicleImages->first();

    $driverFullName = null;
    if (!empty($primaryDriver)) {
        $driverFullName = data_get($primaryDriver, 'full_name')
            ?? data_get($primaryDriver, 'name')
            ?? trim((data_get($primaryDriver, 'first_name', '') . ' ' . data_get($primaryDriver, 'last_name', '')));
        $driverFullName = trim($driverFullName) !== '' ? trim($driverFullName) : null;
    }

    $driverPhone = !empty($primaryDriver)
        ? (
            data_get($primaryDriver, 'phone')
            ?? data_get($primaryDriver, 'phone_number')
            ?? data_get($primaryDriver, 'mobile_phone')
            ?? data_get($primaryDriver, 'gsm')
        )
        : null;

    $formattedKm = number_format((float) ($currentKm ?? 0), 0, ',', '.');

    $dateCard = function ($date) {
        if (!$date) {
            return ['text' => '-', 'status' => 'Tanımsız', 'class' => 'bg-slate-100 text-slate-600'];
        }

        $days = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($date)->startOfDay(), false);

        if ($days < 0) {
            return [
                'text' => \Carbon\Carbon::parse($date)->format('d.m.Y'),
                'status' => 'Süresi Geçmiş',
                'class' => 'bg-rose-100 text-rose-700',
            ];
        }

        if ($days <= 30) {
            return [
                'text' => \Carbon\Carbon::parse($date)->format('d.m.Y'),
                'status' => $days . ' gün kaldı',
                'class' => 'bg-amber-100 text-amber-700',
            ];
        }

        return [
            'text' => \Carbon\Carbon::parse($date)->format('d.m.Y'),
            'status' => $days . ' gün kaldı',
            'class' => 'bg-emerald-100 text-emerald-700',
        ];
    };

    $inspectionInfo = $dateCard($vehicle->inspection_date);
    $exhaustInfo = $dateCard($vehicle->exhaust_date);
    $insuranceInfo = $dateCard($vehicle->insurance_end_date);
    $kaskoInfo = $dateCard($vehicle->kasko_end_date);

    $tabClass = function ($key) use ($activeTab) {
        return $activeTab === $key
            ? 'inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg'
            : 'inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition';
    };

    $remainingBadge = function ($endDate) {
        if (!$endDate) {
            return ['text' => '-', 'class' => 'bg-slate-100 text-slate-600'];
        }

        $days = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($endDate)->startOfDay(), false);

        if ($days < 0) {
            return ['text' => abs($days) . ' gün geçti', 'class' => 'bg-rose-100 text-rose-700'];
        }

        if ($days <= 30) {
            return ['text' => $days . ' gün kaldı', 'class' => 'bg-amber-100 text-amber-700'];
        }

        return ['text' => $days . ' gün kaldı', 'class' => 'bg-emerald-100 text-emerald-700'];
    };
@endphp

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
            <div class="mb-2 font-semibold">Lütfen aşağıdaki hataları düzeltin:</div>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @include('vehicles.partials.header')

    {{-- Sekmeler + sağda vitrin görseli + aktif tab içeriği artık tabs partial içinde yönetiliyor --}}
    @include('vehicles.partials.tabs')

</div>

@include('vehicles.partials.scripts')

@endsection