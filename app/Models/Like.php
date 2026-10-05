<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;


    protected $table = 'post_likes';


    protected $fillable = ['exchange_post_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exchangePost()
    {
        return $this->belongsTo(ExchangePost::class);
    }
}
