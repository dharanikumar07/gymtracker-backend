<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UuidTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'is_onboarding_completed',
        'user_fitness_data',
        'provider_id',
        'provider_name'
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'user_fitness_data' => 'array',
            'is_onboarding_completed' => 'boolean'
        ];
    }

    public function plans()
    {
        return $this->hasMany(Plan::class, 'user_uuid', 'uuid');
    }

    public function physicalActivityPlan()
    {
        return $this->hasOne(Plan::class, 'user_uuid', 'uuid')->where('type', 'physical_activity')->where('is_active', true);
    }

    public function sendVeirfyEMailTOUser(): void
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:6500');
        $verificationUrl = $frontendUrl . '/verify-email/' . $this->uuid . '/' . sha1($this->getEmailForVerification());

        Mail::send('emails.custom-verify-email', ['url' => $verificationUrl], function ($message) {
            $message->to($this->email);
            $message->subject('Verify Email Address');
        });
    }
}
