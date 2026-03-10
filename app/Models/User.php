<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{

    // to define the uuid id
    use HasUuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $incrementing = false;


    protected $fillable = [
        'name',
        'email',
        'password',
        'gcpa',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function semesters() {
        return $this->hasMany(Semester::class);
    }
}
