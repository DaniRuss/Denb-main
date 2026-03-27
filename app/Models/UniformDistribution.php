<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniformDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'item_type',
        'size',
        'quantity',
        'distribution_date',
        'distribution_type',
        'reason',
        'issued_by',
        'notes',
    ];

    protected $casts = [
        'distribution_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
