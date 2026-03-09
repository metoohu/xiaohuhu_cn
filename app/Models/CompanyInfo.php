<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInfo extends Model
{
    use HasFactory;

    protected $table = 'company_info';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'abbreviation',
        'contact_number',
        'nature_business',
        'capture_time',
    ];
}

