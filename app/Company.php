<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table      = 'companies';
    protected $primaryKey = 'id';
    public $timestamps    = false;

    protected $fillable = [
        'attributes', 'customs', 'relationships','created_at', 'updated_at'
    ];

    const NOT_ACTIVE_STATUS = 113375;
}
