<?php

namespace Trinavo\TrinaCrud\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Trinavo\TrinaCrud\Traits\HasCrud;

class TrinaCrudColumn extends Model
{
    use HasFactory;
    use HasCrud;

    protected $guarded = [];

    protected $fillable = [
        'trina_crud_model_id',
        'column_name',
        'column_db_type',
        'column_user_type',
        'column_label',
        'required',
        'default_value',
        'grid_order',
        'edit_order',
        'size',
        'hide',
        'created_at',
        'updated_at',
    ];

    //cast
    protected $casts = [
        'required' => 'boolean',
        'hide' => 'boolean',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];

    public function trinaCrudModel(): BelongsTo
    {
        return $this->belongsTo(TrinaCrudModel::class);
    }
}
