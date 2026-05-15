<?php

namespace App\Filament\Widgets;

use App\Enums\ListingStatus;
use App\Models\Chat;
use App\Models\Listing;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users',      User::count()),
            Stat::make('Active Listings',  Listing::where('status', ListingStatus::Active)->count()),
            Stat::make('Pending Review',   Listing::where('status', ListingStatus::Pending)->count()),
            Stat::make('Total Chats',      Chat::count()),
        ];
    }
}
