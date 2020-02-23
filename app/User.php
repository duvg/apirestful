<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use App\Transformers\UserTransformer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    // Verificación del usuario
    const USER_VERIFIED = '1';
    const USER_UNVERIFIED = '0';

    // Verificar el rol del usuario
    const USER_ADMIN = 'true';
    const USER_REGULAR = 'false';

    protected $table = 'users';
    protected $dates = ['deleted_at'];

    // Transformer
    public $transformer = UserTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verified',
        'verification_token',
        'admin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
    */ 
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token'
    ];




    // Accessors & Mutators (getters and setters)
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    public function getNameAttribute($value)
    {
        return ucwords($value);    
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    

    public function isVerified()
    {
        return $this->verified == User::USER_VERIFIED;
    }

    public function isAdministrator()
    {
        return $this->admin == User::USER_ADMIN;
    }

    public static function generateVerificationToken()
    {
        return str_random(40);
    }
}
