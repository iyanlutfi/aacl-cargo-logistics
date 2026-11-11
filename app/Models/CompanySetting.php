<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_tagline',
        'company_address',
        'company_phone',
        'company_email',
        'cn_prefix',
    ];
}
