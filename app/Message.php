<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;
    protected $connection = 'pgsql';
    protected $table = 'messages';
    protected $fillable = [
        'user_id' ,'room_id','parent_id',
        'message' , 'seen', 'file' , 'edited' , 'like_count'
    ];
    protected $dates = ['deleted_at'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

}
