<?php

namespace KluseG\LaravelWallets\Events;

use Illuminate\Queue\SerializesModels;
use KluseG\LaravelWallets\Models\Wallet;

class WalletUpdated
{
    use SerializesModels;

    /**
     * Wallet instace.
     *
     * @var \KluseG\LaravelWallets\Models\Wallet
     */
    public $wallet;

    /**
     * Create a new event instance.
     *
     * @param  \KluseG\LaravelWallets\Models\Wallet $wallet
     * @return void
     */
    public function __construct(Wallet $wallet)
    {
        $this->wallet = $wallet;
    }
}
