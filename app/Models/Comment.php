<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // Ownership and post relationship are assigned by the server.
    protected $fillable = ['content', 'rating', 'parent_id'];

    // คอมเมนต์เป็นของผู้ใช้งาน 1 คน
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // คอมเมนต์อยู่ใต้โพสต์ 1 โพสต์
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
