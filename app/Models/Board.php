<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'bg_color',
        'bg_img',
        'status',
        'user_id',
        'workspace_id'

    ];

    public function user(){
        return $this->belongsTo(User::class);
    }


    public function workspace(){
        return $this->belongsTo(Workspace::class);

    }


    public  function stages(){
        return $this->belongsToMany(Stage::class, 'board_stage')
                    ->withPivot('order');

    }

    public  function features(){
        return $this->belongsToMany(Feature::class, 'feature_stage',"feature_id","board_id")
            ->withPivot('order','stage_id');
    }
}
