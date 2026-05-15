<?php

namespace App\Filament\Resources\Listings\Schemas;

use App\Enums\ListingStatus;
use App\Models\Amenity;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('owner_id')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('area'),
                TextInput::make('road_and_house')
                    ->default(null),
                TextInput::make('type')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('৳'),
                TextInput::make('deposit')
                    ->numeric()
                    ->default(null),
                TextInput::make('beds')
                    ->required()
                    ->numeric(),
                TextInput::make('baths')
                    ->required()
                    ->numeric(),
                TextInput::make('size')
                    ->numeric()
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('coord_x')
                    ->numeric()
                    ->default(null),
                TextInput::make('coord_y')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options(collect(ListingStatus::cases())->mapWithKeys(
                        fn (ListingStatus $s) => [$s->value => $s->label()]
                    ))
                    ->required()
                    ->default(ListingStatus::Pending->value),
                CheckboxList::make('amenities')
                    ->relationship('amenities', 'label')
                    ->columns(3)
                    ->columnSpanFull(),
                TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}