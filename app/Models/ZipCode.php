<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
        protected $fillable = ['zip_code','state','country','country_code'];
   
}
