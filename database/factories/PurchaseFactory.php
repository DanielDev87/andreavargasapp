<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'status' => 'pending',
            'payment_method' => null,
            'bold_payment_id' => null,
        ];
    }
}
