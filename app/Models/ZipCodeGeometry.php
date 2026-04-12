<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipCodeGeometry extends Model
{
    protected $table = 'zip_code_geometries';

    protected $fillable = [
        'zip_code_id',
        'zip_code',
        'geom',
        'centroid',
    ];

    /**
     * Relationship: belongs to ZipCode
     */
    public function zipCode()
    {
        return $this->belongsTo(ZipCode::class);
    }
}
