<?php

namespace App\Models;

use App\Core\Domain\Enum\User\UserBloodType;
use App\Core\Domain\Enum\User\UserGender;
use App\Core\Domain\Enum\User\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\PaymentMethod;
use App\Models\PaymentConcept;
use App\Models\Payment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements MustVerifyEmail
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
        'phone_number',
        'birthdate',
        'gender',
        'curp',
        'address',
        'password',
        'stripe_customer_id',
        'blood_type',
        'registration_date',
        'status'
    ];


    public function paymentConcepts(){
        return $this->belongsToMany(PaymentConcept::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);

    }

    public function paymentMethods(){
        return $this->hasMany(PaymentMethod::class);
    }

    public function studentDetail(){
        return $this->hasOne(StudentDetail::class);
    }

    public function children()
    {
        return $this->hasMany(ParentStudent::class, 'parent_id');
    }

    public function parents()
    {
        return $this->hasMany(ParentStudent::class, 'student_id');
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
            'birthdate' => 'date',
            'registration_date' => 'date',
            'address' => 'array',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'gender' => UserGender::class,
            'blood_type' => UserBloodType::class,
            'status' => UserStatus::class
        ];
    }



}
