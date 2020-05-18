<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Verification extends Model
{
    use SoftDeletes;
    protected $connection = 'pgsql';
    protected $table = 'verifications';
    protected $dates = ['deleted_at'];
    protected $fillable = ['code' , 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
