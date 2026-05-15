<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\Pages\CreateUser;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('password_hash')
                    ->label('Password')
                    ->password()
                    ->placeholder(fn ($livewire) => $livewire instanceof CreateUser ? null : 'Leave blank to keep current password')
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($livewire) => $livewire instanceof CreateUser),
                Select::make('role')
                    ->options([
                        'renter' => 'Renter',
                        'owner'  => 'Owner',
                        'admin'  => 'Admin',
                    ])
                    ->required()
                    ->default('renter'),
                DatePicker::make('date_of_birth'),
                Toggle::make('is_complete'),
            ]);
    }
}
