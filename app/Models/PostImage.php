<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    use HasFactory;


    public $timestamps = false;

    protected $fillable = ['image_path'];


    protected $appends = ['image_url'];

    public function exchangePost()
    {
        return $this->belongsTo(ExchangePost::class);
    }


    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
