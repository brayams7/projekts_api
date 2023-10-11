<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attachment extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'description',
        'created_at',
        'uuid',
        'url',
        'attachment_type_id'
    ];

    public function attachmentType(): BelongsTo
    {
        return $this->belongsTo(AttachmentType::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class,'feature_attachment');
    }
}
