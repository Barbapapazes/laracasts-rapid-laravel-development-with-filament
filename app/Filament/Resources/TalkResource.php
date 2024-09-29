<?php

namespace App\Filament\Resources;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Resources\TalkResource\Pages;
use App\Models\Talk;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Talk::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filter');
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->description(function (Talk $record) {
                        return Str::of($record->abstract)->limit(40);
                    }),
                Tables\Columns\ImageColumn::make('speaker.avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?name=' . urlencode($record->speaker->name);
                    }),
                Tables\Columns\TextColumn::make('speaker.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('new_talk'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(function ($state) {
                        return $state->getColor();
                    }),
                Tables\Columns\IconColumn::make('length')
                    ->icon(function ($state) {
                        return match ($state) {
                            TalkLength::NORMAL => 'heroicon-o-megaphone',
                            TalkLength::LIGHTNING => 'heroicon-o-flash',
                            TalkLength::KEYNOTE => 'heroicon-o-star',
                        };
                    }),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('new_talk'),
                Tables\Filters\SelectFilter::make('speaker')
                    ->relationship('speaker', 'name')
                    ->preload()
                    ->searchable()
                    ->multiple(),
                Tables\Filters\Filter::make('has_avatar')
                    ->label('Show only speakers with avatars')
                    ->query(function ($query) {
                        return $query->whereHas('speaker', function ($query) {
                            return $query->whereNotNull('avatar');
                        });
                    })

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->visible(function (Talk $record) {
                            return $record->status === TalkStatus::SUBMITTED;
                        })
                        ->action(function (Talk $record) {
                            $record->approve();
                        })
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->after(function () {
                            Notification::make()
                                ->duration(3000)
                                ->title('Talk approved')
                                ->body('The talk has been approved.')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('reject')
                        ->visible(function (Talk $record) {
                            return $record->status === TalkStatus::SUBMITTED;
                        })
                        ->action(function (Talk $record) {
                            $record->reject();
                        })
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->after(function () {
                            Notification::make()
                                ->duration(3000)
                                ->title('Talk rejected')
                                ->body('The talk has been rejected.')
                                ->danger()
                                ->send();
                        })
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->tooltip('Export Talks')
                    ->action(
                        function ($livewire) {
                            Log::info($livewire->getFilteredTableQuery()->count());
                        }
                    ),
            ]);;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTalks::route('/'),
            'create' => Pages\CreateTalk::route('/create'),
            // 'edit' => Pages\EditTalk::route('/{record}/edit'),
        ];
    }
}
