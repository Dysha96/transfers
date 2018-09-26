<?php

namespace App\Http\Controllers;

use App\CashFlow;
use App\PlannedTransaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RootController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
        $users = $user->all();
        $date = date('Y-m-d\TH:00');
        return view('welcome', ['users' => $users, 'date' => $date]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param PlannedTransaction        $plannedTransaction
     * @param CashFlow                  $cashFlow
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(
        Request $request,
        PlannedTransaction $plannedTransaction,
        CashFlow $cashFlow
    ) {
        $message = [
            'senderUser.required' => 'Необходимо указать отправителя',
            'different' => 'Нельзя отпарвить самому себе',
            'senderUser.numeric' => 'Неверно указан отправитель',
            'exists' => 'Такого пользователя не существует',
            'recipientUser.required' => 'Необходимо указать получателя',
            'recipientUser.numeric' => 'Неверно указан отправитель',
            'amount.required' => 'Необходимо указать сумму',
            'date.required' => 'Необходимо указать дату',
            'date.date' => 'Неверно указана дата, нужно в формате 2018-09-26 7:00',
            'date.after' => 'В прошлое нельзя :(',
        ];
        $this->validate($request, [
            'senderUser' => 'required|numeric|different:recipientUser|exists:users,id',
            'recipientUser' => 'required|numeric|exists:users,id',
            'amount' => 'required|max:9999999|min:0.01',
            'date' => 'required|date|after:now',
        ], $message);

        $requestInput = $request->input();

        try {
            DB::beginTransaction();
            $userBalance = $cashFlow->balanceById($requestInput['senderUser']);
            $plannedTransfer
                = $plannedTransaction->plannedTransferById($requestInput['senderUser']);

            if ($userBalance - $plannedTransfer < $requestInput['amount']) {
                DB::rollBack();
                return redirect()->back()->with('danger', "Недостаточно средств");
            }
            $plannedTransaction->create([
                'sender_user_id' => $requestInput['senderUser'],
                'recipient_user_id' => $requestInput['recipientUser'],
                'amount' => $requestInput['amount'],
                'planned_date' => $requestInput['date'],
                'status' => 'WAITING'
            ]);
            DB::commit();
            Log::debug("Создана транзакция");
            return redirect()->back()->with(
                'success',
                "Транзакция запланирована и будет осуществлена {$requestInput['date']}"
            );
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', "Произошла ошибка с БД");
        }
    }
}
