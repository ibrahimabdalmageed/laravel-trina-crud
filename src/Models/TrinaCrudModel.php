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


    public function columns()
    {
        return $this->hasMany(TrinaCrudColumn::class);
    }
}
