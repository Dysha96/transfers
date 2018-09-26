<?php

namespace Tests\Unit;

use App\CashFlow;
use App\PlannedTransaction;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @property User user
 * @property PlannedTransaction plannedTransaction
 */
class TransactionTest extends TestCase
{
    use WithoutMiddleware;
    use DatabaseTransactions;

    private $cashFlow;
    private $plannedTransaction;

    public function setUp()
    {
        parent::setUp();
        $this->cashFlow = new CashFlow();
        $this->plannedTransaction = new PlannedTransaction();
    }

    public function testCreateTransactionInThePast()
    {
        $nowDate = date('Y-m-d\TH:00', mktime(0, 0, 0, 7, 1, 2000));

        $response = $this->json('POST', '/', [
            'senderUser' => 1,
            'recipientUser' => 2,
            'amount' => 1,
            'date' => $nowDate,
        ]);

        $content = json_decode($response->getContent());
        $this->assertNotEmpty($content);
        $errors = $content->errors;
        $this->assertNotEmpty($errors);
        $this->assertNotEmpty($errors->date);
        $this->assertTrue(in_array('В прошлое нельзя :(', $errors->date));
    }

    public function testNonexistentUser()
    {
        $nowDate = date('Y-m-d\TH:00', mktime(0, 0, 0, 7, 1, 2040));

        $response = $this->json('POST', '/', [
            'senderUser' => 0,
            'recipientUser' => 2,
            'amount' => 1,
            'date' => $nowDate,
        ]);

        $content = json_decode($response->getContent());
        $this->assertNotEmpty($content);
        $errors = $content->errors;
        $this->assertNotEmpty($errors);
        $this->assertNotEmpty($errors->senderUser);
        $this->assertTrue(in_array('Такого пользователя не существует', $errors->senderUser));
    }

    public function testSendToMyself()
    {
        $nowDate = date('Y-m-d\TH:00', mktime(0, 0, 0, 7, 1, 2040));

        $response = $this->json('POST', '/', [
            'senderUser' => 1,
            'recipientUser' => 1,
            'amount' => 1,
            'date' => $nowDate,
        ]);

        $content = json_decode($response->getContent());
        $this->assertNotEmpty($content);
        $errors = $content->errors;
        $this->assertNotEmpty($errors);
        $this->assertNotEmpty($errors->senderUser);
        $this->assertTrue(in_array('Нельзя отпарвить самому себе', $errors->senderUser));

    }


    public function testNotEnoughMoney()
    {
        $nowDate = date('Y-m-d\TH:00', mktime(0, 0, 0, 7, 1, 2035));
        $senderUser = 1;
        $recipientUser = 2;
        $amount = 9999999;
        $response = $this->json('POST', '/', [
            'senderUser' => $senderUser,
            'recipientUser' => $recipientUser,
            'amount' => $amount,
            'date' => $nowDate,
        ]);

        $response->assertSessionHas('danger');
        $this->assertEquals("Недостаточно средств", session()->get('danger'));
        $this->assertDatabaseMissing(
            'planned_transactions',
            [
                'sender_user_id' => $senderUser,
                'recipient_user_id' => $recipientUser,
                'amount' => $amount,
                'planned_date' => $nowDate,
                'status' => 'WAITING',
            ]
        );
    }

    public function testCreateTransaction()
    {
        $nowDate = date('Y-m-d\TH:00', mktime(0, 0, 0, 7, 1, 2035));
        $senderUser = 1;
        $recipientUser = 2;
        $amount = 5;

        $response = $this->json('POST', '/', [
            'senderUser' => $senderUser,
            'recipientUser' => $recipientUser,
            'amount' => $amount,
            'date' => $nowDate,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals("Транзакция запланирована и будет осуществлена {$nowDate}", session()->get('success'));
        $this->assertDatabaseHas(
            'planned_transactions', [
                'sender_user_id' => $senderUser,
                'recipient_user_id' => $recipientUser,
                'amount' => $amount,
                'planned_date' => $nowDate,
                'status' => 'WAITING',
            ]
        );

    }

    public function testMethodExecutionTransactions()
    {
        $nowDate = date('Y-m-d\TH:00', mktime(0, 0, 0, 7, 1, 2010));
        $senderUser = 1;
        $recipientUser = 2;
        $amount = 6;


        $this->plannedTransaction->create([
            'sender_user_id' => $senderUser,
            'recipient_user_id' => $recipientUser,
            'amount' => $amount,
            'planned_date' => $nowDate,
            'status' => 'WAITING'
        ]);

        $this->assertDatabaseHas(
            'planned_transactions', [
                'sender_user_id' => $senderUser,
                'recipient_user_id' => $recipientUser,
                'amount' => $amount,
                'planned_date' => $nowDate,
                'status' => 'WAITING',
            ]
        );

        $this->plannedTransaction->executionTransactions($this->cashFlow);

        $this->assertDatabaseHas(
            'planned_transactions', [
                'sender_user_id' => $senderUser,
                'recipient_user_id' => $recipientUser,
                'amount' => $amount,
                'planned_date' => $nowDate,
                'status' => 'SUCCESS',
            ]
        );

    }
}
