<?php

namespace App\Filament\Resources\CaseCommunications\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CaseCommunicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Section::make('Communication Details')
                    ->schema([
                        \Filament\Forms\Components\Select::make('caseable_type')
                            ->label('Case Type')
                            ->options([
                                'App\Models\Complaint' => 'Complaint',
                                'App\Models\Tip' => 'Tip',
                            ])
                            ->required()
                            ->reactive(),

                        \Filament\Forms\Components\Select::make('caseable_id')
                            ->label('Case ID')
                            ->options(function (callable $get) {
                                $type = $get('caseable_type');
                                if (!$type) return [];
                                return $type::pluck($type === 'App\Models\Complaint' ? 'ticket_number' : 'tip_number', 'id');
                            })
                            ->searchable()
                            ->required(),

                        \Filament\Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        \Filament\Forms\Components\Select::make('direction')
                            ->options([
                                'incoming' => 'Incoming',
                                'outgoing' => 'Outgoing',
                            ])
                            ->required(),

                        \Filament\Forms\Components\Select::make('channel')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'portal' => 'Portal',
                                'in_person' => 'In Person',
                            ])
                            ->required(),

                        \Filament\Forms\Components\TextInput::make('contact_email')
                            ->email(),

                        \Filament\Forms\Components\TextInput::make('contact_phone')
                            ->tel(),

                        Textarea::make('message')
                            ->required()
                            ->columnSpanFull(),

                        \Filament\Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
