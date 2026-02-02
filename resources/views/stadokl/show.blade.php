<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Dokladu: ') . ($staDokl->doklad->KlicDokla ?? 'N/A') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6 flex justify-between items-center">
                        <a href="{{ route('stadokl.index') }}" class="text-blue-600 hover:text-blue-900 inline-block">&larr; Zpět na seznam</a>
                        <livewire:doklad.print-doklad-label :dokladId="$staDokl->doklad->KlicDokla ?? $staDokl->Doklad" :doklad="$staDokl->doklad" />
                    </div>

                    {{ $staDokl->doklad->SysPrimKlicDokladu }}

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Základní informace</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Klíč Dokladu</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->doklad->KlicDokla ?? 'N/A' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">MPS Projekt</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->doklad->MPSProjekt ?? '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Termín Datum</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->doklad->TerminDatum ?? '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Typ Pohybu</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->TypPohybu ?? '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Vyhodnocení</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->Vyhodnoceni ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="border rounded-lg p-4 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Vazby</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Vlastní Osoba</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->doklad->vlastniOsoba->Prijmeni ?? '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Zakázka (Klíč)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->doklad->rodicZakazka->KlicDokla ?? '-' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Specifický Symbol</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $staDokl->doklad->rodicZakazka->SpecifiSy ?? '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-8 border rounded-lg overflow-hidden bg-white shadow-sm">
                        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Položky dokladu
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Č. řádku
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Položka
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Text
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Množství
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cena
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($staDokl->doklad->radky ?? [] as $radek)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $radek->CisloRadk }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $radek->Polozka }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $radek->TxtRadku }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format((float)$radek->MnozstviZMJ, 2) }} {{ $radek->ZaklMerJednotka }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format((float)$radek->ProdCeZaZMJvM1D, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                Žádné položky nenalezeny.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
