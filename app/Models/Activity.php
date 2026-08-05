<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['workspace_id', 'user_id', 'type', 'message', 'context'])]
class Activity extends Model
{
    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
