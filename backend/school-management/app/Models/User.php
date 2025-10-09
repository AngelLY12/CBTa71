<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Career;
use App\Models\PaymentMethod;
use App\Models\PaymentConcept;
use App\Models\Payment;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
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
        'career_id',
        'stripe_customer_id',
        'registration_date',
        'status'
    ];

    public function career(){
        return $this->belongsTo(Career::class);
    }

    public function paymentConcepts(){
        return $this->belongsToMany(PaymentConcept::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function paymentMethods(){
        return $this->hasMany(PaymentMethod::class);
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
