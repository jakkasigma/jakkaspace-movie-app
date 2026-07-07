<?php

namespace App\Models;

use Database\Factories\MovieListFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovieList extends Model
{
    /** @use HasFactory<MovieListFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'code',
        'cover_photo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listMovies(): HasMany
    {
        return $this->hasMany(ListMovie::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ListMember::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ListMessage::class);
    }

    public function approvedMembers(): HasMany
    {
        return $this->hasMany(ListMember::class)->where('status', 'approved');
    }

    public function pendingMembers(): HasMany
    {
        return $this->hasMany(ListMember::class)->where('status', 'pending');
    }

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
