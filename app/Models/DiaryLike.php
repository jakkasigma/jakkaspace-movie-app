<?php

namespace App\Models;

use Database\Factories\DiaryLikeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaryLike extends Model
{
    /** @use HasFactory<DiaryLikeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'diary_entry_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diaryEntry(): BelongsTo
    {
        return $this->belongsTo(DiaryEntry::class);
    }
}
