<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'grace_ends_at',
        'billing_cycle',
        'pricing_model',
        'price',
        'currency',
        'student_count',
        'amount_due',
        'amount_paid',
        'payment_status',
        'payment_reference',
        'activated_by',
        'upgraded_from_subscription_id',
        'downgraded_from_subscription_id',
        'superseded_by_subscription_id',
        'plan_name_snapshot',
        'price_snapshot',
        'billing_cycle_snapshot',
        'pricing_model_snapshot',
        'features_snapshot',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'price' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'price_snapshot' => 'decimal:2',
        'features_snapshot' => 'array',
        'metadata' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function activatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function upgradedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'upgraded_from_subscription_id');
    }

    public function downgradedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'downgraded_from_subscription_id');
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_subscription_id');
    }
}