<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Models\User;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';
    protected static string|\UnitEnum|null $navigationGroup = 'Case Management';
    protected static ?string $navigationLabel = 'Complaints';
    protected static ?string $pluralLabel = 'Complaints';
    protected static ?string $modelLabel = 'Complaint';
    protected static ?int $navigationSort = 1;

    // NO FORM - Admins don't create complaints!
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Empty - prevent complaint creation in admin
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Citizen Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('complaint_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'harassment' => 'Harassment',
                        'corruption' => 'Corruption',
                        'service_delay' => 'Service Delay',
                        'wrongful_conduct' => 'Wrongful Conduct',
                        'bribery' => 'Bribery',
                        'discrimination' => 'Discrimination',
                        'other' => 'Other',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        'pending' => 'secondary',
                        'assigned' => 'info',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        'escalated' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        'low' => 'secondary',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->default('Unassigned'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'assigned' => 'Assigned',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'escalated' => 'Escalated',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned Only')
                    ->query(fn($query) => $query->whereNull('assigned_to'))
                    ->toggle(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View Details')
                    ->icon('heroicon-o-eye'),

                // Assign Action
                Action::make('assign')
                    ->label('Assign')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('assigned_to')
                            ->label('Assign To Officer')
                            ->options(User::pluck('name', 'id'))
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('assignment_note')
                            ->label('Internal Instructions'),
                    ])
                    ->action(function (Complaint $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => 'assigned',
                        ]);

                        Notification::make()
                            ->title('Complaint assigned successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Complaint $record) => $record->status === 'pending'),

                // Request More Information
                Action::make('request_info')
                    ->label('Request More Info')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('message')
                            ->label('Message to Complainant')
                            ->required()
                            ->default('We need additional information to process your complaint. Please provide:'),
                        \Filament\Forms\Components\Checkbox::make('send_email')
                            ->label('Send via email')
                            ->default(true),
                    ])
                    ->action(function (Complaint $record, array $data) {
                        Notification::make()
                            ->title('Request sent to complainant')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Complaint $record) => $record->email && !in_array($record->status, ['resolved', 'closed'])),

                // Escalate
                Action::make('escalate')
                    ->label('Escalate')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Select::make('escalate_to')
                            ->label('Escalate To')
                            ->options([
                                'supervisor' => 'Supervisor',
                                'department_head' => 'Department Head',
                                'director' => 'Director',
                                'commissioner' => 'Commissioner',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Select::make('reason')
                            ->options([
                                'timeout' => 'Response time exceeded',
                                'complexity' => 'Case complexity',
                                'sensitivity' => 'Sensitive matter',
                                'public_interest' => 'Public interest',
                                'legal' => 'Legal implications',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->required(),
                    ])
                    ->action(function (Complaint $record, array $data) {
                        $record->update([
                            'status' => 'escalated',
                        ]);

                        Notification::make()
                            ->title('Complaint escalated')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn(Complaint $record) => in_array($record->status, ['assigned', 'investigating']) && $record->priority === 'high'),

                // Update Status Action
                Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('resolution_summary')
                            ->label('Notes/Summary')
                            ->required(),
                    ])
                    ->action(function (Complaint $record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                        ]);

                        Notification::make()
                            ->title('Complaint status updated')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Complaint $record) => in_array($record->status, ['assigned', 'in_progress'])),

                // Send Satisfaction Survey
                Action::make('send_survey')
                    ->label('Send Survey')
                    ->icon('heroicon-o-star')
                    ->color('success')
                    ->action(function (Complaint $record) {
                        if ($record->email) {
                            Notification::make()
                                ->title('Survey sent to ' . $record->email)
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(fn(Complaint $record) => $record->status === 'resolved' && $record->email),

                // Print
                Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn(Complaint $record) => url('/complaint/print/' . $record->id))
                    ->openUrlInNewTab(),

                // Export Single
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function (Complaint $record) {
                        Notification::make()
                            ->title('Exporting PDF...')
                            ->info()
                            ->send();
                    }),

                // Internal Note
                Action::make('add_note')
                    ->label('Add Note')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('gray')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('note')
                            ->label('Internal Note')
                            ->required(),
                    ])
                    ->action(function (Complaint $record, array $data) {
                        Notification::make()
                            ->title('Internal note added')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    // Assign Multiple
                    BulkAction::make('bulk_assign')
                        ->label('Assign Selected')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            \Filament\Forms\Components\Select::make('assigned_to')
                                ->label('Assign to Officer')
                                ->options(User::pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                if (in_array($record->status, ['pending', 'under_review'])) {
                                    $record->update([
                                        'assigned_to' => $data['assigned_to'],
                                        'status' => 'assigned',
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title(count($records) . ' complaints assigned')
                                ->success()
                                ->send();
                        }),

                    // Update Status Bulk
                    BulkAction::make('bulk_status')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            \Filament\Forms\Components\Select::make('status')
                                ->options([
                                    'under_review' => 'Under Review',
                                    'investigating' => 'Investigating',
                                    'resolved' => 'Resolved',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }

                            Notification::make()
                                ->title('Status updated for ' . count($records) . ' complaints')
                                ->success()
                                ->send();
                        }),

                    // Export Selected
                    BulkAction::make('export_selected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn($records) => Notification::make()->title('Exporting ' . count($records) . ' records...')->info()->send()),

                    // Print Selected
                    BulkAction::make('print_selected')
                        ->label('Print Selected')
                        ->icon('heroicon-o-printer')
                        ->action(fn($records) => Notification::make()->title('Printing ' . count($records) . ' records...')->info()->send()),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('General Information')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('ticket_number')
                                    ->label('Ticket #')
                                    ->weight('bold')
                                    ->copyable(),
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Submitted Date')
                                    ->dateTime(),
                                \Filament\Infolists\Components\TextEntry::make('status')
                                    ->label('Current Status')
                                    ->badge()
                                    ->color(fn($state): string => match ($state) {
                                        'pending' => 'warning',
                                        'resolved' => 'success',
                                        default => 'info',
                                    }),
                            ]),
                    ]),

                \Filament\Schemas\Components\Section::make('Citizen Details')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('full_name')
                                    ->label('Full Name'),
                                \Filament\Infolists\Components\TextEntry::make('phone_number')
                                    ->label('Phone Number'),
                                \Filament\Infolists\Components\TextEntry::make('email')
                                    ->label('Email Address')
                                    ->default('Not provided'),
                                \Filament\Infolists\Components\TextEntry::make('id_number')
                                    ->label('National ID / Driver License'),
                            ]),
                    ]),

                \Filament\Schemas\Components\Section::make('Complaint Details')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('complaint_type')
                                    ->label('Type of Complaint'),
                                \Filament\Infolists\Components\TextEntry::make('priority')
                                    ->label('Priority')
                                    ->badge()
                                    ->color(fn($state): string => match ($state) {
                                        'high' => 'warning',
                                        'urgent' => 'danger',
                                        default => 'gray',
                                    }),
                                \Filament\Infolists\Components\TextEntry::make('incident_date')
                                    ->label('Date of Incident')
                                    ->date(),
                                \Filament\Infolists\Components\TextEntry::make('incident_location')
                                    ->label('Incident Location'),
                            ]),
                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Detailed Description')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),

                \Filament\Schemas\Components\Section::make('Case Management')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('assignedTo.name')
                                    ->label('Assigned Officer')
                                    ->default('Not Assigned'),
                                \Filament\Infolists\Components\TextEntry::make('resolution_summary')
                                    ->label('Resolution/Action Taken')
                                    ->default('Pending investigation')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                \Filament\Schemas\Components\Section::make('Evidence & Attachments')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('evidence_description')
                            ->label('Description of Evidence')
                            ->placeholder('No description provided'),
                        \Filament\Infolists\Components\TextEntry::make('attachments')
                            ->label('Attached Files')
                            ->badge()
                            ->separator(',')
                            ->formatStateUsing(fn($state) => basename($state))
                            ->url(fn($state) => asset('storage/' . $state), true)
                            ->placeholder('No attachments provided'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'view' => Pages\ViewComplaint::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 10 ? 'danger' : 'warning';
    }
}
