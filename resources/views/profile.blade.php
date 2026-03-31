<x-app-layout>
    <x-mary-header title="Můj profil" separator></x-mary-header>

    <div class="space-y-6">
        <x-mary-card
            title="Informace o profilu"
            separator
            subtitle="Zde si můžete upravit své jméno nebo e-mailovou adresu."
        >
            <livewire:profile.update-profile-information-form/>

        </x-mary-card>

        @can('can print')
            <x-mary-card
                title="Preferovaná tiskárna"
                separator
                subtitle="Vyberte tiskárnu, která bude použita pro tisk QR štítků."
            >
                <livewire:profile.update-printer-preference/>
            </x-mary-card>
        @endcan

        <x-mary-card
            title="Změna hesla"
            separator
            subtitle="Ujistěte se, že používáte dostatečně dlouhé a bezpečné heslo."
        >
            <livewire:profile.update-password-form/>

        </x-mary-card>

        <x-mary-card
            title="Zaslání odkazu pro obnovu hesla"
            separator
            subtitle="Pokud si nepamatujete heslo, můžete si nechat zaslat odkaz na jeho resetování do e-mailu."
        >
            <livewire:profile.send-password-reset-link/>

        </x-mary-card>


        @can('edit profile')
            <x-mary-card
                title="Smazání účtu"
                separator
                subtitle="Po smazání účtu budou veškerá data trvale odstraněna a tento proces nelze vrátit zpět."
            >
                <livewire:profile.delete-user-form/>

            </x-mary-card>
        @endcan
    </div>


</x-app-layout>
