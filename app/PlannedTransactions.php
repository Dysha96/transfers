<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlannedTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable
        = [
            'sender_user_id',
            'recipient_user_id',
            'amount',
            'planned_date',
            'status'
        ];

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_user_id', 'id');
    }

    public function recipientUser()
    {
        return $this->belongsTo(User::class, 'recipient_user_id', 'id');
    }

    public function plannedTransferById($id)
    {
        return $this
            ->select('amount')
            ->whereRaw("sender_user_id={$id}")
            ->where("status", "WAITING")
            ->sum('amount');
    }

    public function waitingTransfers()
    {
        return $this->where([
            ["status", "WAITING"],
            ['planned_date', '<=', Carbon::now()]
        ])->get();
    }

    public function executionTransactions(CashFlow $cashFlow)
    {
        Log::debug("Запуск транзакций");
        $transactions = $this->waitingTransfers();
        foreach ($transactions as $transaction) {
            try {
                DB::transaction(function () use ($cashFlow, $transaction) {
                    $transaction->update(['status' => 'SUCCESS']);
                    $cashFlow->addOperation($transaction->sender_user_id, -$transaction->amount, 'WITHDRAWAL');
                    $cashFlow->addOperation($transaction->recipient_user_id, $transaction->amount, 'REFILL');
                }, 2);
            } catch (\Exception $exception) {
                Log::debug("Была совершена неуспешная транзакция");
                $transaction->update([
                    'status' => 'REJECT',
                ]);
            }
            Log::debug("Была совершена успешная транзакция");

        }
    }
}
