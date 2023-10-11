<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function feature(){
        return $this->belongsTo(Feature::class, 'feature_id', );
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', );
    }
}
