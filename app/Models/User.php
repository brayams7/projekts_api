<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Constants\Constants;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    private  const ROLES_HIERARCHY=[
        Constants::ROLE_TYPE_ADMIN => [Constants::ROLE_TYPE_MEMBER],
        Constants::ROLE_TYPE_MEMBER => []
    ];

    protected $fillable = [
        'name',
        'email',
        'username',
        'status',
        'password',
        'role_id',
        'picture_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
        'created_at'
    ];

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function session(){
        return $this->belongsTo(Session::class);
    }

    public function workspaces(){
        return $this->hasMany(Workspace::class);
    }

    public function memberWorkspaces(){
        return $this->belongsToMany(Workspace::class,'user_workspace');
    }

    public function features(){
        return $this->belongsToMany(Feature::class,'feature_user');
    }


    public function getJWTIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function  commentsFeature(){
        return $this->hasMany(FeatureComment::class, 'user_id');
    }

    public function assignedFeatures(){
        return $this->belongsToMany(Feature::class, 'feature_user')
            ->withPivot('is_watcher');
    }

    public function assignedTasks(){
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot('is_watcher');
    }


    public function isGranted($roleName){

        if($this->role->name === $roleName) return true;

        return self::isRoleHierarchy($roleName,self::ROLES_HIERARCHY[$this->role->name]);
    }

    private static function isRoleHierarchy($roleName, $roleHierarchy): bool
    {
        if(in_array($roleName,$roleHierarchy)) return true;
        foreach ($roleHierarchy as $roleIncluded){
            if(self::isRoleHierarchy($roleName, self::ROLES_HIERARCHY[$roleIncluded])) return true;

        }
        return  false;

    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];
}
