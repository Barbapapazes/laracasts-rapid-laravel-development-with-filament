<?php

namespace App\Models;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Filament\Forms;

class Talk extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'speaker_id' => 'integer',
        'status' => TalkStatus::class,
        'length' => TalkLength::class,
    ];

    public function speaker(): BelongsTo
    {
        return $this->belongsTo(Speaker::class);
    }

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class);
    }

    public function approve(): void
    {
        $this->status = TalkStatus::APPROVED;
        $this->save();
    }

    public function reject(): void
    {
        $this->status = TalkStatus::REJECTED;
        $this->save();
    }

    public static function getForm($speakerId = null): array
    {
        return [
            Forms\Components\TextInput::make('title')
                ->required(),
            Forms\Components\RichEditor::make('abstract')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Select::make('speaker_id')
                ->hidden(function () use ($speakerId) {
                    return $speakerId !== null;
                })
                ->relationship('speaker', 'name')
                ->required(),
        ];
    }
}
