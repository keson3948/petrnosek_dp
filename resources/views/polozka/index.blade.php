<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Polozky - Index') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <form action="{{ route('polozka.store') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Vytvořit testovací položku
                            </button>
                        </form>
                    </div>

                    @if($polozky->isEmpty())
                        <p class="text-gray-500">Žádné položky nebyly nalezeny.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Klíč
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Název
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Základní jednotka
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Material
                                        </th>
                                        <!-- Add more columns here as needed -->
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($polozky as $polozka)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $polozka->KlicPoloz ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $polozka->Nazev1 ?? 'N/A' }}
                                            </td>
                                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $polozka->ZaklaJedn ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $polozka->Material ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-8 p-4 bg-gray-100 rounded border border-gray-300">
                            <details>
                                <summary class="cursor-pointer font-bold text-blue-600 hover:text-blue-800">
                                    Zobrazit debug data (první položka)
                                </summary>
                                <div class="mt-2 text-xs font-mono overflow-auto max-h-96">
                                    <pre>{{ print_r($polozky->first()->toArray(), true) }}</pre>
                                </div>
                            </details>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
