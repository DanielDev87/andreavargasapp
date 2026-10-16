<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_validated' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Purchases made by the user (one-to-many).
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Validation requests submitted by the user.
     */
    public function validationRequests(): HasMany
    {
        return $this->hasMany(ValidationRequest::class);
    }

    /**
     * Courses the user has access to via purchases.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'purchases')
            ->withPivot(['id', 'status', 'payment_method', 'paid_at'])
            ->withTimestamps();
    }

    /**
     * Certificates issued to the user.
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function badges()
    {
        return $this->hasMany(Badge::class);
    }
}
