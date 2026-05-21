<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public string $captchaCode = '';

    public function mount(): void
    {
        parent::mount();
        $this->generateCaptcha();
    }

    public function generateCaptcha(): void
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $this->captchaCode = $code;
        session(['captcha_code' => $code]);
    }

    protected function buildCaptchaSvg(): HtmlString
    {
        $chars = str_split($this->captchaCode ?: session('captcha_code', '??????'));
        $colors = ['#7c3aed', '#0284c7', '#dc2626', '#059669', '#d97706', '#7c3aed'];
        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='168' height='52'
                    style='background:#f1f5f9;border-radius:10px;border:2px solid #cbd5e1;display:block;margin:0 auto 4px;'>";
        // noise lines
        for ($i = 0; $i < 5; $i++) {
            [$x1, $y1, $x2, $y2] = [random_int(0, 168), random_int(0, 52), random_int(0, 168), random_int(0, 52)];
            $svg .= "<line x1='{$x1}' y1='{$y1}' x2='{$x2}' y2='{$y2}' stroke='#94a3b8' stroke-width='1' opacity='0.5'/>";
        }
        // noise dots
        for ($i = 0; $i < 12; $i++) {
            [$cx, $cy] = [random_int(0, 168), random_int(0, 52)];
            $svg .= "<circle cx='{$cx}' cy='{$cy}' r='2' fill='#94a3b8' opacity='0.4'/>";
        }
        // characters
        foreach ($chars as $i => $char) {
            $x = 14 + ($i * 24);
            $y = random_int(32, 40);
            $r = random_int(-12, 12);
            $s = random_int(22, 28);
            $col = $colors[$i % count($colors)];
            $svg .= "<text x='{$x}' y='{$y}' font-family='Courier New,monospace' font-size='{$s}' font-weight='900'
                        fill='{$col}' transform='rotate({$r},{$x},{$y})'>{$char}</text>";
        }
        $svg .= '</svg>';

        return new HtmlString(
            "<div style='text-align:center;margin-bottom:4px;user-select:none;'>{$svg}</div>"
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent()->hidden(true),

            // Visual CAPTCHA image
            Placeholder::make('captcha_image')
                ->hiddenLabel()
                ->content(fn() => $this->buildCaptchaSvg()),

            // Input: no label, only placeholder
            TextInput::make('captcha_answer')
                ->label('Escriba el captcha')
                ->placeholder('Escribe los 6 caracteres')
                ->required()
                ->maxLength(6),
        ]);
    }

    public function authenticate(): ?\Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        $data = $this->form->getState();
        $answer = strtoupper(trim($data['captcha_answer'] ?? ''));
        $expected = strtoupper(trim(session('captcha_code', '')));

        if ($answer !== $expected || empty($answer)) {
            $this->generateCaptcha();
            Notification::make()
                ->danger()
                ->title('CAPTCHA incorrecto')
                ->body('Escribe exactamente los 6 caracteres que aparecen en la imagen.')
                ->send();
            return null;
        }

        session()->forget('captcha_code');
        return parent::authenticate();
    }
}
