<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Languages lookup model.
 * Created to replace the phantom `language_id=1` default in calls table.
 * Seeded with: Urdu (default), English, Punjabi.
 */
class Language extends Model
{
    protected $fillable = ['code', 'name', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'language_id');
    }
}
