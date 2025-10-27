<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDetail extends Model
{
    protected $table = 'student_details';
    protected $fillable = [
        'user_id',
        'career_id',
        'n_control',
        'semestre',
        'group',
        'workshop'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function career(){
        return $this->belongsTo(Career::class);
    }
}
