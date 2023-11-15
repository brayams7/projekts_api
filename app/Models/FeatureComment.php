<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeatureComment extends Model
{
    use HasFactory, HasUuids;
    public $timestamps = true;


    protected $fillable = [
        'user_id',
        'feature_id',
        'comment'
    ];

    protected $hidden = [
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class, 'feature_id', );
    }

    public function user():BelongsTo{
        return $this->belongsTo(User::class, 'user_id', );
    }

    public function attachments():BelongsToMany{
        return $this->belongsToMany(Attachment::class,'feature_comment_attachment');
    }
}
