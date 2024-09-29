<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Filament\Forms;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Speaker extends Model
{
    use HasFactory;

    const QUALIFICATIONS = [
        'business-leader' => 'Business Leader',
        'charisma' => 'Charismatic Speaker',
        'first-time' => 'First Time Speaker',
        'hometown-hero' => 'Hometown Hero',
        'humanitarian' => 'Works in Humanitarian Field',
        'laracasts-contributor' => 'Laracasts Contributor',
        'twitter-influencer' => 'Large Twitter Following',
        'youtube-influencer' => 'Large YouTube Following',
        'open-source' => 'Open Source Creator / Maintainer',
        'unique-perspective' => 'Unique Perspective'
    ];

    protected $casts = [
        'id' => 'integer',
        'qualifications' => 'array',
    ];

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class);
    }

    public function talks(): HasMany
    {
        return $this->hasMany(Talk::class);
    }

    public static function getForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required(),
            Forms\Components\FileUpload::make('avatar')
                ->avatar()
                ->imageEditor()
                ->directory('avatars')
                ->maxSize(1024 * 1024 * 2),
            Forms\Components\TextInput::make('email')
                ->email()
                ->required(),
            Forms\Components\RichEditor::make('bio')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('twitter_handle'),
            Forms\Components\CheckboxList::make('qualifications')
                ->columnSpanFull()
                ->columns(3)
                ->searchable()
                ->bulkToggleable()
                ->options(
                    self::QUALIFICATIONS
                )
        ];
    }
}
