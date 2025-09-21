<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CareerConcept;
use App\Models\StudentConcept;
use App\Models\SemesterConcept;

class PaymentConcept extends Model
{
    protected $fillable = [
        'concept_name',
        'description',
        'status',
        'start_date',
        'end_date',
        'amount',
        'is_global'
    ];

    // asignaciones
    public function careerConcepts() {
        return $this->hasMany(CareerConcept::class, 'id_concept');
    }

    public function semesterConcepts() {
        return $this->hasMany(SemesterConcept::class, 'id_concept');
    }

    public function studentConcepts() {
        return $this->hasMany(StudentConcept::class, 'id_concept');
    }

    // shortcut: alumnos relacionados
    public function students() {
        return $this->belongsToMany(User::class, 'student_concepts', 'id_concept', 'id_user')
                    ->withTimestamps();
    }

    // shortcut: carreras relacionadas
    public function careers() {
        return $this->belongsToMany(Career::class, 'career_concepts', 'id_concept', 'id_career')
                    ->withTimestamps();
    }
}
