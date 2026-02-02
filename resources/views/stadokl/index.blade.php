<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stav Dokladů') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klíč Dokladu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MPS Projekt</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vlastní Osoba</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zakázka (Klíč)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specifický Symbol</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Termín Datum</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($staDoklady as $item)
                                    <tr>
                                        <!-- A00.KlicDokla -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <a href="{{ route('stadokl.show', ['id' => trim($item->Doklad)]) }}" class="text-blue-600 hover:text-blue-900 hover:underline">
                                                {{ $item->doklad->KlicDokla ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <!-- A00.MPSProjekt -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->doklad->MPSProjekt ?? '-' }}
                                        </td>
                                        <!-- T0300.Prijmeni -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->doklad->vlastniOsoba->Prijmeni ?? '-' }}
                                        </td>
                                        <!-- T0200.KlicDokla (RodicZakazka) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->doklad->rodicZakazka->KlicDokla ?? '-' }}
                                        </td>
                                        <!-- T0200.SpecifiSy (RodicZakazka) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->doklad->rodicZakazka->SpecifiSy ?? '-' }}
                                        </td>
                                        <!-- T0200.SpecifiSy (RodicZakazka) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->doklad->TerminDatum ?? '-' }}
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
