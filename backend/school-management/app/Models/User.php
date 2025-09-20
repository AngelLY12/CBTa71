<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Career;
use App\Models\Payment_method;
use App\Models\Roles;
use App\Models\User_roles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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

    public function user_roles(){
        return $this->hasMany(User_roles::class,'id_user');
    }

    public function roles() {
        return $this->belongsToMany(Roles::class, 'users_roles', 'id_user', 'id_role');
    }

    public function payment_method()
    {
        return $this->hasMany(Payment_method::class,'id_user');

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
