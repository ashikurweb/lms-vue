<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // ========== Helpers ==========

    public static function getOrCreate(?User $user = null, ?string $sessionId = null): self
    {
        if ($user) {
            return static::firstOrCreate(['user_id' => $user->id]);
        }

        return static::firstOrCreate(['session_id' => $sessionId]);
    }

    public function addCourse(Course $course): CartItem
    {
        $existingItem = $this->items()->where('course_id', $course->id)->first();

        if ($existingItem) {
            return $existingItem;
        }

        return $this->items()->create([
            'course_id' => $course->id,
            'price' => $course->price,
        ]);
    }

    public function removeCourse(Course $course): bool
    {
        return $this->items()->where('course_id', $course->id)->delete() > 0;
    }

    public function hasCourse(Course $course): bool
    {
        return $this->items()->where('course_id', $course->id)->exists();
    }

    public function getSubtotal(): float
    {
        return $this->items()->sum('price');
    }

    public function getItemCount(): int
    {
        return $this->items()->count();
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function mergeTo(Cart $targetCart): void
    {
        foreach ($this->items as $item) {
            if (!$targetCart->hasCourse($item->course)) {
                $targetCart->addCourse($item->course);
            }
        }

        $this->delete();
    }
}
