<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_type',
        'category_id',
        'title',
        'description',
        'gadget_name',
        'condition_percent',
        'looking_for',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

    // [เพิ่มใหม่] 1 โพสต์ มีได้หลายคอมเมนต์
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // [เพิ่มใหม่] 1 โพสต์ มีได้หลายไลค์ (จากหลาย user)
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
