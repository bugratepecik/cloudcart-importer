<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiLog extends Model {
    use HasFactory;
    protected $fillable = ['request_type', 'endpoint', 'request_data', 'response_data', 'status_code'];
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array'
    ];
}
