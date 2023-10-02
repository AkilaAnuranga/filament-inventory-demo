<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vendor extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope());
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
