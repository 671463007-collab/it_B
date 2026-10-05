<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;


    protected $fillable = ['content', 'rating', 'parent_id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function exchangePost()
    {
        return $this->belongsTo(ExchangePost::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }
}
