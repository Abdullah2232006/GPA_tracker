<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';
    protected $fillable = ['title', 'credit hours', 'grade'];
    protected $guarded = ['id'];

    public function semester () {
        return $this->belongsTo(Semester::class);
    }
}
