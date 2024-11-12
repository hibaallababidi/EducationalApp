<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Student extends Authenticatable implements JWTSubject, HasMedia
{
    use HasFactory;
    use Notifiable;
    use InteractsWithMedia;


    protected $fillable = [
        'first_name',
        'last_name',
        'status',
        'email',
        'password',
        'phone_number',
        'location_id',
        'email_verified_at',
        'device_token'
    ];

    public function lessons()
    {
        return $this->hasMany(PrivateLesson::class);
    }

    protected $hidden = [
        'password',
    ];

    protected function casts()
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
