<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope());
    }

    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor():BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }




}
