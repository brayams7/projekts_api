<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    protected $fillable = [
        'id',
        'name',
        'description'
    ];
    //relation many to many
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class,'permission_rol');
    }
}
