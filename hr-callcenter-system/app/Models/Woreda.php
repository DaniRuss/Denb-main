<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Woreda extends Model
{
    use HasFactory;

    protected $fillable = ['sub_city_id', 'name_am', 'name_en', 'code'];

    public function subCity()
    {
        return $this->belongsTo(SubCity::class);
    }
}
