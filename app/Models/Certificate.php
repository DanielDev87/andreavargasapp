<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    protected $fillable = ['user_id', 'course_id', 'certificate_path', 'data', 'issued_at'];

    protected $casts = [
        'data' => 'array',
        'issued_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Certificates live on the private disk and are served by CertificateController;
     * ones issued before that change may still be on the public disk until moved.
     */
    public function fileStorage(): FilesystemAdapter
    {
        return Storage::disk('local')->exists($this->certificate_path) ? Storage::disk('local') : Storage::disk('public');
    }
}
