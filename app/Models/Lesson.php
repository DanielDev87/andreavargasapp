<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['module_id', 'title', 'description', 'video_id', 'video_url', 'order', 'duration_seconds', 'meta', 'is_free'];

    protected $casts = [
        'meta' => 'array',
        'is_free' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function course()
    {
        return $this->module?->course();
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }
}
