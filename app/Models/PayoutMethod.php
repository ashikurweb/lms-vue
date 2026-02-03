<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'name',
        'paypal_email',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_routing_number',
        'bank_swift_code',
        'bank_iban',
        'bank_country',
        'details',
        'is_default',
        'is_verified',
    ];

    protected $casts = [
        'details' => 'array',
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
    ];

    // Encrypt sensitive fields
    protected $hidden = [
        'bank_account_number',
        'bank_routing_number',
        'bank_iban',
    ];

    // ========== Relationships ==========

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ========== Scopes ==========

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // ========== Helpers ==========

    public function setAsDefault(): void
    {
        // Remove default from other methods
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    public function verify(): void
    {
        $this->update(['is_verified' => true]);
    }

    public function getMaskedAccountNumber(): string
    {
        if (!$this->bank_account_number) {
            return '';
        }

        return '****' . substr($this->bank_account_number, -4);
    }

    public function getDisplayName(): string
    {
        switch ($this->type) {
            case 'paypal':
                return "PayPal ({$this->paypal_email})";
            case 'bank_transfer':
                return "{$this->bank_name} ({$this->getMaskedAccountNumber()})";
            default:
                return $this->name ?? ucfirst($this->type);
        }
    }
}
