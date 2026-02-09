<x-app-layout>
    <x-mary-header title="Profil" separator></x-mary-header>

    <div class="space-y-6">
        <x-mary-card
            title="Profile Information"
            separator
            subtitle="Update your account's profile information and email address."
        >
            <livewire:profile.update-profile-information-form/>

        </x-mary-card>


        <x-mary-card
            title="Update Password"
            separator
            subtitle="Ensure your account is using a long, random password to stay secure."
        >
            <livewire:profile.update-password-form/>

        </x-mary-card>


        <x-mary-card
            title="Delete Account"
            separator
            subtitle="Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain."
        >
            <livewire:profile.delete-user-form/>

        </x-mary-card>
    </div>


</x-app-layout>
