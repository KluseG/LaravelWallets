<?php

namespace KluseG\LaravelWallets\Events;

use Illuminate\Queue\SerializesModels;

use KluseG\LaravelWallets\Models\WalletTransaction;

class WalletTransactionCreated
{
    use SerializesModels;

    /**
     * Wallet instace
     *
     * @var \KluseG\LaravelWallets\Models\WalletTransaction
     */
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param  \KluseG\LaravelWallets\Models\WalletTransaction $transaction
     * @return void
     */
    public function __construct(WalletTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}