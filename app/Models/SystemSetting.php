<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSetting extends Model
{
    use HasFactory;
    
    protected $fillable = ['key_name', 'value', 'description', 'group_name', 'updated_by'];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key_name', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, ?string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key_name' => $key],
            ['value' => $value, 'group_name' => $group, 'updated_by' => auth()->id()]
        );
    }
}
