<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'image_url',
        'bold_payment_link',
        'features',
        'type',
        'category',
        'rating',
        'event_date',
        'location',
        'capacity',
        'seats_taken',
        'deposit_amount',
        'duration_hours',
        'level',
        'is_hidden',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'rating' => 'decimal:1',
        'event_date' => 'date',
        'is_hidden' => 'boolean',
    ];

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Return all lessons for the course ordered by module.order then lesson.order.
     */
    public function orderedLessons()
    {
        return Lesson::whereIn('module_id', $this->modules()->pluck('id'))
            ->join('modules', 'lessons.module_id', '=', 'modules.id')
            ->orderBy('modules.order')
            ->orderBy('lessons.order')
            ->select('lessons.*');
    }

    public function isPresencial(): bool
    {
        return $this->type === 'presencial';
    }

    public function seatsAvailable(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max(0, $this->capacity - $this->seats_taken);
    }

    public function isSoldOut(): bool
    {
        return $this->isPresencial() && $this->capacity !== null && $this->seatsAvailable() <= 0;
    }

    /**
     * A presencial course whose event date has already passed.
     */
    public function isPastEvent(): bool
    {
        return $this->isPresencial() && $this->event_date !== null && $this->event_date->isPast();
    }

    /**
     * Whether the course should appear in public listings/search — false once the
     * admin manually hides it, or automatically once a presencial event has passed.
     */
    public function isPubliclyVisible(): bool
    {
        return ! $this->is_hidden && ! $this->isPastEvent();
    }
}
