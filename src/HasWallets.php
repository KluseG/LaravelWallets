<?php

namespace KluseG\LaravelWallets;

use KluseG\LaravelWallets\Facades\LaravelWallets as Wallets;

trait HasWallets
{
    /**
     * Creates new Wallet
     */
    public function addWallet(string $crcy, float $total = null)
    {
        return Wallets::setContext($this)->create($crcy, $total);
    }

    /**
     * Returns specified wallet or collection of all wallets
     */
    public function getWallet(string $crcy = null)
    {
        return Wallets::setContext($this)->get($crcy);
    }

    /**
     * Creates transaction with income
     */
    public function income(string $crcy, float $amount)
    {
        return Wallets::setContext($this)->on($crcy)->income($amount);
    }

    /**
     * Creates transaction with outcome
     */
    public function outcome(string $crcy, float $amount)
    {
        return Wallets::setContext($this)->on($crcy)->outcome($amount);
    }

    /**
     * Gets Wallet's total (since specified date or current total)
     */
    public function getTotal(string $crcy, $since = null, $pretty = false)
    {
        return Wallets::setContext($this)->on($crcy)->getTotal($since, $pretty);
    }
    
    /**
     * Gets Wallet's total within specified range
     */
    public function getTotalBetween(string $crcy, $from = null, $to = null, $pretty = false)
    {
        return Wallets::setContext($this)->on($crcy)->getTotal($from, $to, $pretty);
    }
}