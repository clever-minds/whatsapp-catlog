<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    use HasFactory;

    protected $fillable = ['notification_id', 'store_id', 'status'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
