<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function assignedUsers():BelongsToMany{
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('is_watcher');
    }

    public function children():HasMany{
        return $this->hasMany(Task::class,'task_id');
    }

    public function parent():BelongsTo{
        return $this->belongsTo(Task::class,'task_id');
    }

    public function subTasks():HasMany{
        return $this->children()->with(['subTasks']);
    }
}
