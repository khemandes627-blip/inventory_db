<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TransactionLog;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'category',
        'stock',
        'minimum_stock',
        'price',
    ];

    public function transactionLogs()
    {
        return $this->hasMany(TransactionLog::class);
    }
}
