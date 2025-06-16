<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type'];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
                    ->withPivot('joined_at', 'last_read_at')
                    ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    public function getUnreadCountForUser($userId)
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        
        if (!$participant || !$participant->pivot->last_read_at) {
            return $this->messages()->where('user_id', '!=', $userId)->count();
        }

        return $this->messages()
            ->where('created_at', '>', $participant->pivot->last_read_at)
            ->where('user_id', '!=', $userId)
            ->count();
    }
}
