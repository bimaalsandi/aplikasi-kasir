<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page
{
    protected string $view = 'filament.pages.change-password';
    protected static ?string $title = 'Ganti Password Akun';
    protected static ?string $slug = 'ganti-password';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Keamanan Akun')
                    ->description('Pastikan menggunakan password yang kuat untuk menjaga keamanan akun Anda.')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Password Lama')
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->revealable(),

                        TextInput::make('new_password')
                            ->label('Password Baru')
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->same('new_password_confirmation')
                            ->revealable(),

                        TextInput::make('new_password_confirmation')
                            ->label('Konfirmasi Password Baru')
                            ->password()
                            ->required()
                            ->revealable(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        $this->form->fill();

        Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Password akun Anda berhasil diperbarui.')
            ->send();
    }
}
