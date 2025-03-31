<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
