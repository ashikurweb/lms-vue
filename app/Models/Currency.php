<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'symbol_native',
        'exchange_rate',
        'is_default',
        'is_active',
        'decimal_digits',
        'rounding',
        'decimal_separator',
        'thousands_separator',
        'pattern',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'decimal_digits' => 'integer',
        'rounding' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($currency) {
            // Ensure only one currency can be default
            if ($currency->is_default) {
                static::where('id', '!=', $currency->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the default currency.
     */
    public static function getDefault()
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get active currencies.
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Format amount with currency settings.
     */
    public function formatAmount($amount)
    {
        $decimalSeparator = $this->decimal_separator ?? '.';
        $thousandsSeparator = $this->thousands_separator ?? ',';
        $decimalDigits = $this->decimal_digits ?? 2;
        
        $formattedAmount = number_format(
            $amount,
            $decimalDigits,
            $decimalSeparator,
            $thousandsSeparator
        );

        $pattern = $this->pattern ?? '{symbol}{amount}';
        
        return str_replace(
            ['{symbol}', '{amount}'],
            [$this->symbol, $formattedAmount],
            $pattern
        );
    }

    /**
     * Scope to get only active currencies.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only default currency.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
