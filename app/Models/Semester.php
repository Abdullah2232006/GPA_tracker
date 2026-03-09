<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';
    protected $fillable = ['semester', 'gpa'];
    protected $guarded = ['id'];

    public function user () {
        return $this->belongsTo(User::class);
    }

    // define one to many relation ship with courses
    public function courses () {
        return $this->hasMany(Course::class);
    }
}
