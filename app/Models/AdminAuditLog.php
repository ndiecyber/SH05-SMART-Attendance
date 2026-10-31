<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $fillable = ['admin_id', 'action', 'auditable_type', 'auditable_id', 'before_values', 'after_values'];

    protected function casts(): array
    {
        return ['before_values' => 'array', 'after_values' => 'array'];
    }
}
