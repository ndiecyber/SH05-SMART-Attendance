<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = ['location_name', 'office_latitude', 'office_longitude', 'office_address', 'radius_meters', 'allowed_ips', 'gps_verification_required', 'ip_verification_required', 'gps_accuracy_threshold_meters', 'late_grace_period_minutes', 'updated_by'];

    protected function casts(): array
    {
        return ['allowed_ips' => 'array', 'gps_verification_required' => 'boolean', 'ip_verification_required' => 'boolean'];
    }
}
