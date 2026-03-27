<?php
// app/Models/Employee.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'first_name_am',
        'last_name_am',
        'first_name_en',
        'last_name_en',
        'gender',
        'age',
        'email',
        'phone',
        'emergency_contact',
        'sub_city_id',
        'woreda_id',
        'kebele',
        'house_number',
        'position',
        'rank',
        'employee_type',
        'salary',
        'hire_date',
        'birth_date',
        'birthplace',
        'education_level',
        'field_of_study',
        'institution',
        'national_id',
        'ethio_coder',
        'shirt_size',
        'pant_size',
        'shoe_size_casual',
        'shoe_size_leather',
        'hat_size',
        'cloth_size',
        'rain_cloth_size',
        'jacket_size',
        't_shirt_size',
        'training_round',
        'last_training_date',
        'training_notes',
        'status',
        'is_suspended_payment',
        'suspension_reason',
        'suspension_date'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'last_training_date' => 'date',
        'suspension_date' => 'date',
        'is_suspended_payment' => 'boolean',
    ];

    public function subCity()
    {
        return $this->belongsTo(SubCity::class);
    }

    public function woreda()
    {
        return $this->belongsTo(Woreda::class);
    }

    public function uniformDistributions()
    {
        return $this->hasMany(UniformDistribution::class);
    }

    public function getFullNameAmAttribute()
    {
        return $this->first_name_am . ' ' . $this->last_name_am;
    }

    public function getFullNameEnAttribute()
    {
        return $this->first_name_en . ' ' . $this->last_name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeBySubCity($query, $subCityId)
    {
        return $query->where('sub_city_id', $subCityId);
    }
}
