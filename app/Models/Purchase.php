<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'amount', 'status', 'payment_method', 'bold_payment_id', 'bold_reference', 'bold_payment_link', 'bold_payment_url', 'paid_at', 'attendance_code', 'checked_in_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Characters usable in an attendance code, minus visually ambiguous ones (0/O, 1/I/L)
     * so staff can read it back from a screen or a printed confirmation without mistakes.
     */
    private const ATTENDANCE_CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Mark the purchase as completed, unlocking course access. Idempotent — calling this
     * more than once (e.g. a retried webhook) has no further effect. For presencial courses,
     * this is also the single place that increments the taken-seats count, so a course goes
     * "sold out" automatically as its capacity fills, and where the attendance code used to
     * check in on the event day is generated.
     */
    public function markCompleted(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        $this->update(['status' => 'completed', 'paid_at' => now()]);

        if ($this->course->isPresencial()) {
            $this->course->increment('seats_taken');
            $this->update(['attendance_code' => self::generateAttendanceCode()]);
        }

        return true;
    }

    /**
     * Unique reference for Bold (letters, digits and "-", max 60 characters). The timestamp
     * keeps it unique even if purchase ids restart, e.g. after a database reset.
     */
    public function newBoldReference(): string
    {
        return 'AV-'.$this->id.'-'.now()->format('YmdHis');
    }

    /**
     * Apply a payment Bold reports as approved, but only if it covers this purchase:
     * a payment for a different amount or currency never unlocks the course.
     */
    public function confirmBoldPayment(int|float|string|null $paidTotal, ?string $currency, ?string $transactionId): bool
    {
        $expected = (int) round((float) $this->amount);

        if ($currency !== 'COP' || (int) round((float) $paidTotal) !== $expected) {
            Log::warning('Bold payment does not match the purchase', [
                'purchase_id' => $this->id,
                'expected' => $expected,
                'paid' => $paidTotal,
                'currency' => $currency,
            ]);

            return false;
        }

        if ($transactionId) {
            $this->update(['bold_payment_id' => $transactionId]);
        }

        return $this->markCompleted();
    }

    /**
     * Bold voided (refunded) the payment: course access is revoked and, for presencial
     * courses, the seat and attendance code are released. Idempotent.
     */
    public function cancelBoldPayment(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        $this->update(['status' => 'cancelled', 'attendance_code' => null]);

        if ($this->course->isPresencial() && $this->course->seats_taken > 0) {
            $this->course->decrement('seats_taken');
        }

        return true;
    }

    /**
     * Record attendance for this purchase. Idempotent — a code that's already been
     * checked in (e.g. scanned twice) doesn't overwrite the original check-in time.
     */
    public function checkIn(): bool
    {
        if ($this->checked_in_at !== null) {
            return false;
        }

        $this->update(['checked_in_at' => now()]);

        return true;
    }

    private static function generateAttendanceCode(): string
    {
        do {
            $code = collect(range(1, 8))
                ->map(fn () => self::ATTENDANCE_CODE_ALPHABET[random_int(0, strlen(self::ATTENDANCE_CODE_ALPHABET) - 1)])
                ->implode('');
        } while (self::where('attendance_code', $code)->exists());

        return $code;
    }
}
