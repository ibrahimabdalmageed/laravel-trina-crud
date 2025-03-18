<?php

namespace Trinavo\TrinaCrud\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrinaCrudColumn extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function trinaCrudModel(): BelongsTo
    {
        return $this->belongsTo(TrinaCrudModel::class);
    }
}
