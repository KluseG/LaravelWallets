<?php

namespace KluseG\LaravelWallets\Observers;

use KluseG\LaravelWallets\Models\WalletTransaction as Transaction;

class WalletTransactionObserver
{
    /**
     * Handle to the Transaction "created" event.
     *
     * @param  \KluseG\LaravelWallets\Models\WalletTransaction $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
        if ($transaction->income) {
            $transaction->wallet()->increment('total', $transaction->amount);
            $transaction->wallet()->increment('all_time_total', $transaction->amount);
        } else {
            $transaction->wallet()->decrement('total', $transaction->amount);
        }
    }
}
