<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdClick extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'user_id',
        'page',
        'device_type',
        'user_agent',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    /**
     * Relationships
     */
    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
