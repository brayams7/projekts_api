<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'feature_id',
        'created_at',
        'due_date',
        'task_before',
        'task_after',
        'starts_at',
        'task_id',
        'calculated_time'
    ];

    public function feature(): BelongsTo {
        return $this->belongsTo(Feature::class);
    }

    public function tags():BelongsToMany{
        return $this->belongsToMany(Tag::class, 'task_tag');
    }
}
