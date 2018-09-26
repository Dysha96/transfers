<?php

namespace App\Http\Controllers;

use App\PlannedTransaction;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param PlannedTransaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function index(PlannedTransaction $transaction)
    {
        $transactions = $transaction
            ->join('users AS users1', 'users1.id', 'planned_transactions.sender_user_id')
            ->join('users AS users2', 'users2.id', 'planned_transactions.recipient_user_id')
            ->whereRaw(
                'planned_transactions.id =
                (select max(PT2.id)
                 from planned_transactions PT2
                 where planned_transactions.sender_user_id = PT2.sender_user_id
                and PT2.status = \'SUCCESS\')'
            )
            ->select(
                'users1.first_name AS first_name1',
                'users1.last_name AS last_name1',
                'users2.first_name AS first_name2',
                'users2.last_name AS last_name2',
                'recipient_user_id',
                'amount',
                'planned_date'
            )
            ->get();

        return view('transactions', ['transactions' => $transactions]);
    }
}
