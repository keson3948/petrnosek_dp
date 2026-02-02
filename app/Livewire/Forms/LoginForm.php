<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required_without:rfid|string|email')]
    public string $email = '';

    #[Validate('required_without:rfid|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    #[Validate('required_without:email|string')]
    public string $rfid = '';

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function authenticateRFID(): void
    {
        #$this->ensureIsNotRateLimited();

        $user = \App\Models\User::where('rfid_tag', $this->rfid)->first();

        if (! $user) {
            #RateLimiter::hit($this->throttleKey());

            $this->rfid = '';

            throw ValidationException::withMessages([
                'form.rfid' => 'Neplatný RFID čip.',
            ]);
        }

        Auth::login($user);

        RateLimiter::clear($this->throttleKey());
    }


    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
