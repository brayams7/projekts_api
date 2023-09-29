<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'id',
        'pivot',
        'created_at',
        'updated_at'
    ];

    //relation many to many
    public function roles(){
        return $this->belongsToMany(Role::class,'permission_rol');
    }
}
