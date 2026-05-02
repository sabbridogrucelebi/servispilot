@extends('layouts.app')

@section('title', 'Trafik Cezasını Düzenle')
@section('subtitle', 'Mevcut trafik cezası kaydını güncelleyin')

@section('content')
    <form action="{{ route('traffic-penalties.update', $trafficPenalty) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        @include('traffic-penalties.partials.form', [
            'trafficPenalty' => $trafficPenalty,
            'vehicles' => $vehicles,
            'buttonText' => 'Değişiklikleri Kaydet'
        ])
    </form>
@endsection
