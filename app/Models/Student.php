<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'address',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }
}
