<?php

namespace App\Filament\Resources\Listings\Tables;

use App\Enums\ListingStatus;
use App\Models\AppNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('owner.name')->label('Owner')->searchable(),
                TextColumn::make('area')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('price')->money('BDT')->sortable(),
                TextColumn::make('beds')->numeric()->sortable(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (ListingStatus $state) => $state->label())
                    ->color(fn (ListingStatus $state) => $state->color()),
                TextColumn::make('views')->numeric()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === ListingStatus::Pending)
                    ->action(function ($record) {
                        $record->update(['status' => ListingStatus::Active]);
                        AppNotification::create([
                            'user_id'      => $record->owner_id,
                            'kind'         => 'listing',
                            'title'        => 'Your listing was approved!',
                            'body'         => $record->title . ' is now live.',
                            'reference_id' => $record->id,
                        ]);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === ListingStatus::Pending)
                    ->action(function ($record) {
                        $record->update(['status' => ListingStatus::Rejected]);
                        AppNotification::create([
                            'user_id'      => $record->owner_id,
                            'kind'         => 'listing',
                            'title'        => 'Your listing was rejected.',
                            'body'         => $record->title . ' was not approved. Contact support for details.',
                            'reference_id' => $record->id,
                        ]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}