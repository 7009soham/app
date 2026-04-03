<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function citizens()
    {
        return $this->hasMany(Citizen::class);
    }

    public function waterTaxRecords()
    {
        return $this->hasMany(WaterTaxRecord::class);
    }

    public function propertyTaxRecords()
    {
        return $this->hasMany(PropertyTaxRecord::class);
    }

    public function propertyAssessments()
    {
        return $this->hasMany(PropertyAssessment::class);
    }
}
