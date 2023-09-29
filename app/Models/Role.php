<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];

    //relation many to many
    public function permissions(){
        return $this->belongsToMany(Permission::class,'permission_rol');
    }
}
