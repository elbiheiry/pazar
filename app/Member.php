<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Member extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'member';
    protected $primaryKey = 'member_id';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','username','api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function orders()
    {
        return $this->hasMany('App\Order' , 'member_id');
    }
    
    public function posts()
    {
        return $this->hasMany('App\Post' , 'member_id');
    }
    
    public function wishlists()
    {
        return $this->hasMany('App\Wishlist' , 'member_id');
    }
}