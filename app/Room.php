<?php

namespace App;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes , SoftCascadeTrait;
    protected $connection = 'pgsql';
    protected $table = 'rooms';
    protected $fillable = [
        'type','admin_id'
    ];
    protected $softCascade = ['messages'];
    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsToMany(User::class , 'user_room');
    }

    public function messages()
    {
        return $this->belongsTo(Message::class);
    }
}
