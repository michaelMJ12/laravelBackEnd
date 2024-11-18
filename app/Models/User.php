<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


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


     // Role-based permissions
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    public function isCampaignManager()
    {
        return $this->role === 'campaign_manager';
    }

    // Permissions Methods
    public function canCreateCampaign()
    {
        return $this->isAdmin() || $this->isStaff() || $this->isCampaignManager();
    }

    public function canViewCampaign()
    {
        return $this->isAdmin() || $this->isStaff() || $this->isCampaignManager();
    }

    public function canUpdateCampaign()
    {
        return $this->isAdmin() || $this->isCampaignManager();
    }

    public function canDeleteCampaign()
    {
        return $this->isAdmin();
    }

    public function canManageUsers()
    {
        return $this->isAdmin();
    }


     // New methods for managing users specifically
    public function canCreateUser()
    {
        return $this->isAdmin(); 
    }

    public function canUpdateUser()
    {
        return $this->isAdmin(); 
    }

    public function canDeleteUser()
    {
        return $this->isAdmin(); 
    }


}
