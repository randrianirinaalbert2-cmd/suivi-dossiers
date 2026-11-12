<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation de la requête.
     */
    public function rules(): array
    {
        return [
            'courriel' => ['required', 'string', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ];
    }

    /**
     * Tenter d'authentifier l'utilisateur.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifiants = [
            'courriel' => $this->input('courriel'),
            'password' => $this->input('mot_de_passe'),
        ];

        if (! Auth::attempt($identifiants, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'courriel' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Vérifier que la connexion n'est pas bloquée par trop de tentatives.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'courriel' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Clé de limitation des tentatives de connexion.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('courriel')).'|'.$this->ip());
    }
}
