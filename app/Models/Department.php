<?php
// app/Models/Department.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_am',
        'name_en',
        'code',
        'description',
        'head_of_department_id'
    ];

    public function headOfDepartment()
    {
        return $this->belongsTo(User::class, 'head_of_department_id');
    }

    public function officers()
    {
        return $this->hasMany(Officer::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'assigned_department');
    }

    public function tips()
    {
        return $this->hasMany(Tip::class, 'assigned_department');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'department_users')
            ->withTimestamps();
    }
}
