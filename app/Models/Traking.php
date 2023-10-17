<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Traking extends Model
{
    use HasFactory, HasUuids;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'task_id',
        'hours',
        'minutes',
        'full_minutes',
        'description',
        'date',
        'day',
        'month',
        'year',
    ];

    public function task():BelongsTo{
        return $this->belongsTo(Task::class);
    }

    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }
}
