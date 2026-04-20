@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Maaşlar</h1>
        <a href="{{ route('payrolls.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow">
            Yeni Maaş Kaydı Ekle
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-3">Ay</th>
                    <th class="text-left px-4 py-3">Şoför</th>
                    <th class="text-left px-4 py-3">Ana Maaş</th>
                    <th class="text-left px-4 py-3">Ek Ödeme</th>
                    <th class="text-left px-4 py-3">Kesinti</th>
                    <th class="text-left px-4 py-3">Avans</th>
                    <th class="text-left px-4 py-3">Net Maaş</th>
                    <th class="text-left px-4 py-3">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                    <tr class="border-t">
                        <td class="px-4 py-3">{{ $payroll->period_month }}</td>
                        <td class="px-4 py-3">{{ $payroll->driver?->full_name }}</td>
                        <td class="px-4 py-3">{{ number_format($payroll->base_salary, 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3">{{ number_format($payroll->extra_payment, 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3">{{ number_format($payroll->deduction, 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3">{{ number_format($payroll->advance_payment, 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3 font-semibold">{{ number_format($payroll->net_salary, 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3 space-x-2">
                            <a href="{{ route('payrolls.edit', $payroll) }}" class="text-blue-600">Düzenle</a>

                            <form action="{{ route('payrolls.destroy', $payroll) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600" onclick="return confirm('Bu maaş kaydını silmek istediğine emin misin?')">
                                    Sil
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                            Henüz maaş kaydı yok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection