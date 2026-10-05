<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['reported_user_id', 'exchange_post_id', 'reason'];


    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }


    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function exchangePost()
    {
        return $this->belongsTo(ExchangePost::class);
    }
}
