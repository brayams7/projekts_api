<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory, HasUuids;
    public $timestamps = false;

    protected $fillable = [
        'tag',
        'color'
    ];

    public function tasks():BelongsToMany{
        return $this->belongsToMany(Task::class, 'task_tag');
    }
}
