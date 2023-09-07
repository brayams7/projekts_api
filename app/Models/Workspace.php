<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'initials',
        'color',
        'status',
        'user_id',
        'workspace_type_id'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function workspaceType(){
        return $this->belongsTo(WorkspaceType::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function members(){
        return $this->belongsToMany(User::class,'user_workspace');
    }

    public function boards(){
        return $this->hasMany(Board::class);
    }
}
