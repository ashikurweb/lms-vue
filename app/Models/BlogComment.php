<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'guest_name',
        'guest_email',
        'content',
        'status',
        'likes_count',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'likes_count' => 'integer',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(BlogComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(BlogComment::class, 'parent_id');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessors
    public function getAuthorNameAttribute()
    {
        return $this->user ? $this->user->name : $this->guest_name;
    }

    public function getAuthorEmailAttribute()
    {
        return $this->user ? $this->user->email : $this->guest_email;
    }
}
