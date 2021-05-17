<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        'module_code',
        'module_name',
        'module_term'
    ];  
}
