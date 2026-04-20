@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Yeni Maaş Kaydı Ekle</h1>

    <form action="{{ route('payrolls.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block mb-1">Şoför</label>
                <select name="driver_id" class="w-full border rounded px-3 py-2">
                    <option value="">Seçiniz</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->full_name }}
                        </option>
                    @endforeach
                </select>
                @error('driver_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Ay</label>
                <input type="month" name="period_month" value="{{ old('period_month', now()->format('Y-m')) }}" class="w-full border rounded px-3 py-2">
                @error('period_month') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Ana Maaş</label>
                <input type="number" step="0.01" name="base_salary" value="{{ old('base_salary') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Ek Ödeme</label>
                <input type="number" step="0.01" name="extra_payment" value="{{ old('extra_payment', 0) }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Kesinti</label>
                <input type="number" step="0.01" name="deduction" value="{{ old('deduction', 0) }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block mb-1">Avans</label>
                <input type="number" step="0.01" name="advance_payment" value="{{ old('advance_payment', 0) }}" class="w-full border rounded px-3 py-2">
            </div>
        </div>

        <div class="mt-4">
            <label class="block mb-1">Notlar</label>
            <textarea name="notes" rows="4" class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded">
                Kaydet
            </button>
        </div>
    </form>
@endsection