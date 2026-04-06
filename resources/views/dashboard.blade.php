<x-app-layout>
    <div class="mb-6">
        @can('manage production records')
            <livewire:dashboard.vedouci-dashboard />
        @else
            <livewire:dashboard.production-tracker />
        @endcan
    </div>
</x-app-layout>
