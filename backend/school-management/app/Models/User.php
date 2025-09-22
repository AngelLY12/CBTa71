<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Career;
use App\Models\PaymentMethod;
use App\Models\StudentConcept;
use App\Models\PaymentConcept;
use App\Models\Payment;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'n_control',
        'semestre',
        'phone_number',
        'birthdate',
        'gender',
        'curp',
        'address',
        'state',
        'municipality',
        'password',
        'id_career',
        'registration_date',
        'status'
    ];

    public function career(){
        return $this->belongsTo(Career::class,'id_career');
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class,'id_user');

    }

    public function studentConcepts(){
        return $this->hasMany(StudentConcept::class,'id_user');
    }
    public function payments(){
        return $this->hasMany(Payment::class,'id_user');
    }

    public function paymentConcepts() {
        return $this->belongsToMany(PaymentConcept::class, 'student_concepts', 'id_user', 'id_concept')
                    ->withTimestamps();
    }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $casts = [
        'birthdate' => 'date',
        'registration_date' => 'date',
        'status' => 'boolean',
    ];

}
