<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'enrollment_id',
        'instructor_id',
        'course_id',
        'total_amount',
        'instructor_amount',
        'platform_fee',
        'charity_amount',
        'payment_date',
        'payment_method',
        'payment_id',
        'transaction_id',
        'status',
    ];
    
    protected $casts = [
        'payment_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'instructor_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'charity_amount' => 'decimal:2',
    ];
    
    /**
     * Get the enrollment that generated this revenue
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
    
    /**
     * Get the instructor who earned this revenue
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
    
    /**
     * Get the course associated with this revenue
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
