<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCity extends Model
{
    use HasFactory;

    protected $fillable = ['name_am', 'name_en', 'code'];

    public function woredas()
    {
        return $this->hasMany(Woreda::class);
    }
}
