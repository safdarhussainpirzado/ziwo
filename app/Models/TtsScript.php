<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TtsScript extends Model
{
    protected $fillable = ['title', 'language', 'content', 'audio_path', 'updated_by'];
}
