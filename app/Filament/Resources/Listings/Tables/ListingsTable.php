<?php

namespace App\Filament\Resources\Listings\Tables;

use App\Enums\ListingStatus;
use App\Models\AppNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('listingType.label')->label('Type')->badge(),
                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => '৳ ' . number_format($state))
                    ->sortable(),
                TextColumn::make('beds')->sortable(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (ListingStatus $state) => $state->label())
                    ->color(fn (ListingStatus $state) => $state->color()),
                TextColumn::make('views')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('All Statuses')
                    ->options(collect(ListingStatus::cases())->mapWithKeys(
                        fn (ListingStatus $s) => [$s->value => $s->label()]
                    ))
                    ->query(fn ($query, array $data) =>
                        filled($data['value'])
                            ? $query->where('status', $data['value'])
                            : $query
                    ),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
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
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn ($record) => $record->status === ListingStatus::Pending)
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Reason for rejection')
                            ->placeholder('e.g. Photos are blurry, price seems incorrect...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'           => ListingStatus::Rejected,
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        AppNotification::create([
                            'user_id'      => $record->owner_id,
                            'kind'         => 'listing',
                            'title'        => 'Your listing was rejected.',
                            'body'         => $data['rejection_reason'],
                            'reference_id' => $record->id,
                        ]);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
