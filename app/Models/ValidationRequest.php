<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationRequest extends Model
{
    protected $fillable = ['user_id', 'course_id', 'diploma_path', 'id_document_path', 'status', 'ai_score', 'ai_report', 'reviewed_by', 'reviewed_at'];

    protected $casts = [
        'ai_report' => 'array',
        'ai_score' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
