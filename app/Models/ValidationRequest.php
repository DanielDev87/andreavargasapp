<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class ValidationRequest extends Model
{
    protected $fillable = ['user_id', 'course_id', 'diploma_path', 'id_document_path', 'review_method', 'data_consent_at', 'data_policy_version', 'status', 'ai_score', 'ai_report', 'reviewed_by', 'reviewed_at'];

    protected $casts = [
        'ai_report' => 'array',
        'ai_score' => 'float',
        'reviewed_at' => 'datetime',
        'data_consent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Documents are stored on the private disk; requests made before that change
     * still have theirs on the public disk.
     */
    public function documentStorage(string $path): FilesystemAdapter
    {
        return Storage::disk('local')->exists($path) ? Storage::disk('local') : Storage::disk('public');
    }
}
