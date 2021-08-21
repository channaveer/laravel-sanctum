<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'email', 'password', 'profile_pic', 'provider_type', 'provider_token', 'provider_token_validity', 'activity_notification', 'blocked_at', 'email_verified_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'profile_pic'           => 'array',
        'activity_notification' => 'boolean'
    ];

    public function getIsBlockedAttribute()
    {
        return !empty($this->blocked_at) ? true : false;
    }

    public function getIsEmailVerifiedAttribute()
    {
        return !empty($this->email_verified_at) ? true : false;
    }
}
