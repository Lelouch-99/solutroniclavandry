<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activation extends Model
{
    protected $fillable = [
        'activation_id',
        'controller_id',
        'machine_id',
        'activation_type',
        'reference',
        'customer_id',
        'admin_user',
        'amount',
        'status',
        'message',
        'sent_at',
        'processed_at',
    ];
}