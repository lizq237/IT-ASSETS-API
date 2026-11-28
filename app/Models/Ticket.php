<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Ticket extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'organization_id',
        'asset_id',
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'image_path',
        'comments',
        'last_comment_by',
        'assigned_technician_id',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'organization_id' => 'integer',
        'asset_id' => 'integer',
        'user_id' => 'integer',
        'last_comment_by' => 'integer',
        'assigned_technician_id' => 'integer',
        'resolved_at' => 'datetime',
    ];
}
