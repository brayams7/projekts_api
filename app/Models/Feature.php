<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use \Illuminate\Database\Eloquent\Relations\HasMany;


class Feature extends Model
{
    use HasFactory, HasUuids;
    public $timestamps = true;

    protected $fillable = [
        'title',
        'description',
        'board_id',
        'stage_id',
        'order',
        'due_date',
    ];

    public  function stages():BelongsToMany{
        return $this->belongsToMany(Stage::class, 'feature_stage',"feature_id","stage_id")
            ->withPivot('order','board_id');
    }

    public function boards():BelongsToMany{
        return $this->belongsToMany(Board::class, 'feature_stage',"feature_id","board_id")
            ->withPivot('order','stage_id');
    }

    public function members():BelongsToMany{
        return $this->belongsToMany(User::class, 'feature_user')
            ->withPivot('is_watcher');
    }

    public function commentsUser():HasMany{
        return $this->hasMany(FeatureComment::class, 'feature_id');
    }

    public function assignedUsers():BelongsToMany{
        return $this->belongsToMany(User::class, 'feature_user',)
            ->withPivot('is_watcher');
    }

    public function attachments():BelongsToMany{
        return $this->belongsToMany(Attachment::class, 'feature_attachment');
    }
}
