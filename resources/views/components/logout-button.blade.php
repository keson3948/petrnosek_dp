<form method="POST" action="{{ route('logout') }}" class="inline">
    @csrf
    <x-mary-button type="submit" icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="Odhlásit se" no-wire-navigate>
    </x-mary-button>
</form>

