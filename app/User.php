<?php

namespace App;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use SoftDeletes , Notifiable , SoftCascadeTrait;
    protected $connection = 'pgsql';
    protected $table = 'users';
    protected $softCascade = ['messages' , 'verifications'];
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','username', 'email', 'password','active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class , 'user_room');
    }

    public function likedMessages()
    {
        return $this->belongsToMany(Message::class , 'user_like');
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }
}
