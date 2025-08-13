<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth as LaravelAuth;
use Illuminate\Validation\ValidationException;

class CodeLogin extends BaseLogin
{
    use WithRateLimiting;

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')
                ->label('Código')
                ->numeric()
                ->minLength(6)
                ->maxLength(6)
                ->rule('digits:6')
                ->required()
                ->autofocus()
                ->autocomplete('one-time-code')
                ->extraInputAttributes(['inputmode' => 'numeric', 'pattern' => '\\d{6}'])
                ->validationMessages([
                    'digits' => 'El código debe tener exactamente 6 dígitos.',
                    'min' => 'El código debe tener 6 dígitos.',
                    'max' => 'El código debe tener 6 dígitos.',
                ]),
        ])->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();
        $code = trim((string)($data['code'] ?? ''));

    if ($code === '' || !ctype_digit($code) || strlen($code) !== 6) {
            $this->throwFailureValidationException();
        }

        // Buscar usuario por login_code
        $userModel = config('auth.providers.users.model');
        $user = $userModel::where('login_code', $code)->first();

        if (! $user) {
            $this->throwFailureValidationException();
        }

        // Iniciar sesión directamente sin password
        LaravelAuth::guard(config('auth.defaults.guard', 'web'))->login($user, true);

        // Verificar acceso al panel actual
        $panel = Filament::getCurrentPanel();
        if (($user instanceof FilamentUser) && (! $user->canAccessPanel($panel))) {
            Filament::auth()->logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();
        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.code' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Ingresar')
            ->submit('authenticate');
    }
}
