<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['message'];

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class);
    }
}
