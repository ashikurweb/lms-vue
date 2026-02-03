<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use HasFactory;

    protected $table = 'search_history';

    protected $fillable = [
        'user_id',
        'session_id',
        'query',
        'results_count',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ========== Helpers ==========

    public static function record(string $query, int $resultsCount, array $filters = [], ?User $user = null, ?string $sessionId = null): self
    {
        return static::create([
            'user_id' => $user?->id,
            'session_id' => $sessionId,
            'query' => $query,
            'results_count' => $resultsCount,
            'filters' => $filters,
        ]);
    }

    public static function getPopularSearches(int $limit = 10): \Illuminate\Support\Collection
    {
        return static::select('query')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('query');
    }
}
