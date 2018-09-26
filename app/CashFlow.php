<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
            'user_id',
            'amount',
            'type'
        ];

    public function balanceById($id)
    {
        return $this
            ->select('amount')
            ->whereRaw("user_id={$id}")
            ->sum('amount');
    }

    public function addOperation($userId, $amount, $type)
    {
        $this->create([
            'user_id' => $userId,
            'amount' => $amount,
            'type' => $type
        ]);
    }
}
