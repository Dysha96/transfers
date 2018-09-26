<?php

namespace App\Console\Commands;

use App\CashFlow;
use App\PlannedTransaction;
use Illuminate\Console\Command;

class TransactionsStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start of transactions with the status of waiting';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $transactions = new PlannedTransaction();
        $transactions->executionTransactions(new CashFlow());
    }
}
