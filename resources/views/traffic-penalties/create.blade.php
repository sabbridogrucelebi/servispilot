@extends('layouts.app')

@section('title', 'Yeni Trafik Cezası')
@section('subtitle', 'Yeni trafik cezası kaydı oluşturun')

@section('content')
    <form action="{{ route('traffic-penalties.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @include('traffic-penalties.partials.form', [
            'trafficPenalty' => null,
            'vehicles' => $vehicles,
            'buttonText' => 'Cezayı Kaydet'
        ])
    </form>
@endsection