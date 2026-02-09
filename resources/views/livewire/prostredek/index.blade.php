<div>
    @foreach($prostredky as $p)
        <x-mary-list-item :item="$p" value="KlicProstredku" sub-value="NazevUplny">
            <x-slot:avatar>
                <x-mary-icon name="o-rocket-launch" class="w-12 h-12 bg-primary text-white p-2 rounded-full" />
            </x-slot:avatar>
        </x-mary-list-item>
    @endforeach
</div>
