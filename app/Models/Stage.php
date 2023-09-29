<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $hidden = [
        //'pivot',
    ];

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_default',
        'is_final'
    ];

    public function boards(){
        return $this->belongsToMany(Board::class,'board_stage')
                    ->withPivot('order');
    }

    public function features(){
        return $this->belongsToMany(Feature::class,"feature_stage","stage_id","feature_id")
                ->withPivot("order",'board_id');
    }

}
