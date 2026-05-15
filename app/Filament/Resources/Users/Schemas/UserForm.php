<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password_hash')
                    ->password()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('role')
                    ->required()
                    ->default('renter'),
                DatePicker::make('date_of_birth'),
                TextInput::make('avatar_url')
                    ->url()
                    ->default(null),
                Toggle::make('is_complete')
                    ->required(),
            ]);
    }
}
