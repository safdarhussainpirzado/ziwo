<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhonebookContact extends Model
{
    use HasFactory;

    protected $table = 'phonebook_contacts';

    protected $fillable = [
        'name',
        'phone_number',
        'category', // beat, sector, zone, emergency, custom
        'created_by',
        'is_favorite',
        'metadata',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'metadata' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
