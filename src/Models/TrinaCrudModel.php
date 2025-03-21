<?php

namespace Trinavo\TrinaCrud\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;

class TrinaCrudModel extends Model
{
    use HasFactory;
    use HasCrud;

    protected $guarded = [];

    protected $fillable = [
        'class_name',
        'model_name',
        'model_short',
        'caption',
        'multi_caption',
        'page_size',
        'order_by',
    ];

    //cast
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    ];


    public function columns()
    {
        return $this->hasMany(TrinaCrudColumn::class);
    }
}
