<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['client_id', 'saldo'];

    public function client()
    {
        return $this->belongsToMany(Client::class);
    }
}
