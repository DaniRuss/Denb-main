<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsMessageResource\Pages;
use App\Models\SmsMessage;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SmsMessageResource extends Resource
{
    protected static ?string $model = SmsMessage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string
    {
        return app()->getLocale() === 'am' ? 'ቅጣት እና እርምጃ' : 'Penalty & Action';
    }

    public static function getNavigationLabel(): string
    {
        return app()->getLocale() === 'am' ? 'የSMS መልዕክቶች' : 'SMS Messages';
    }

    public static function getModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'SMS' : 'SMS';
    }

    public static function getPluralModelLabel(): string
    {
        return app()->getLocale() === 'am' ? 'የSMS መልዕክቶች' : 'SMS Messages';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('to')->disabled(),
            Forms\Components\TextInput::make('template_key')->disabled(),
            Forms\Components\TextInput::make('status')->disabled(),
            Forms\Components\Textarea::make('body')->disabled()->columnSpanFull()->rows(6),
            Forms\Components\TextInput::make('provider_message_id')->disabled(),
            Forms\Components\Textarea::make('error')->disabled()->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        $am = app()->getLocale() === 'am';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label($am ? 'ቀን' : 'Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to')
                    ->label($am ? 'ለ' : 'To')
                    ->searchable(),
                Tables\Columns\TextColumn::make('template_key')
                    ->label($am ? 'አይነት' : 'Template')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label($am ? 'ሁኔታ' : 'Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued'    => 'gray',
                        'sent'      => 'warning',
                        'delivered' => 'success',
                        'failed'    => 'danger',
                        default     => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('driver')
                    ->label($am ? 'አቅራቢ' : 'Driver')
                    ->badge(),
                Tables\Columns\TextColumn::make('violator.full_name_am')
                    ->label($am ? 'ደንብ ተላላፊ' : 'Violator')
                    ->searchable(),
                Tables\Columns\TextColumn::make('body')
                    ->label($am ? 'መልዕክት' : 'Message')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label($am ? 'ተልኳል' : 'Sent')
                    ->dateTime()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('error')
                    ->label($am ? 'ስህተት' : 'Error')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'queued'    => 'Queued',
                        'sent'      => 'Sent',
                        'delivered' => 'Delivered',
                        'failed'    => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('template_key')
                    ->options([
                        'penalty_receipt'    => $am ? 'ቅጣት ደረሰኝ' : 'Penalty Receipt',
                        'warning_24h'        => $am ? 'የ24 ሰዓት ማስጠንቀቂያ' : '24h Warning',
                        'warning_3d'         => $am ? 'የ3 ቀን ማስጠንቀቂያ' : '3-Day Warning',
                        'payment_overdue'    => $am ? 'ክፍያ ያለፈ' : 'Overdue',
                        'court_filed'        => $am ? 'ክስ ቀርቧል' : 'Court Filed',
                        'compliance_thanks'  => $am ? 'ምስጋና' : 'Thanks',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsMessages::route('/'),
            'view'  => Pages\ViewSmsMessage::route('/{record}'),
        ];
    }
}
