@if(\App\Models\Terminal::isTerminal() && auth()->check())
    <div
        x-data="{
            timer: null,
            timeout: 30000,
            reset() {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.logout(), this.timeout);
            },
            logout() {
                document.getElementById('terminal-idle-logout-form')?.submit();
            }
        }"
        x-init="
            reset();
            ['mousemove','mousedown','click','keydown','touchstart','wheel','scroll'].forEach(ev =>
                window.addEventListener(ev, () => reset(), { passive: true })
            );
        "
        class="hidden"
    ></div>
    <form id="terminal-idle-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
@endif
