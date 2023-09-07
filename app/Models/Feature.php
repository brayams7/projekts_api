<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'title',
        'description',
        'board_id',
        'stage_id',
        'order',
        'due_date',
    ];

    public  function stages(){
        return $this->belongsToMany(Stage::class, 'feature_stage',"feature_id","stage_id")
            ->withPivot('order','board_id');
    }

    public function boards(){
        return $this->belongsToMany(Board::class, 'feature_stage',"feature_id","board_id")
            ->withPivot('order','stage_id');
    }
}
