<?php

namespace KluseG\LaravelWallets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use KluseG\LaravelWallets\Models\Wallet;
use KluseG\LaravelWallets\Models\WalletTransaction;
use KluseG\LaravelWallets\Exceptions\WalletEmptyException;
use KluseG\LaravelWallets\Exceptions\InvalidContextException;
use KluseG\LaravelWallets\Exceptions\WalletNotFoundException;
use KluseG\LaravelWallets\Exceptions\WalletDuplicateException;

class LaravelWallets
{
    /**
     * Eloquent Model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $ctx;

    /**
     * Current Wallet instance.
     *
     * @var \KluseG\LaravelWallets\Models\Wallet
     */
    protected $wallet;

    /**
     * Relation instance.
     *
     * @var \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    protected $wallets;

    /**
     * Creates new Wallet with given currency and optional initial income.
     *
     * @param   string      $crcy   Currency name in XYZ format
     * @param   float|null  $total  Initial wallet total
     *
     * @return  \KluseG\LaravelWallets\Models\Wallet
     *
     * @throws \KluseG\LaravelWallets\Exceptions\WalletDuplicateException
     */
    public function create(string $crcy, float $total = null) : Wallet
    {
        $this->checkContext();

        if ($this->walletExists($crcy)) {
            throw new WalletDuplicateException(trans('wallets::exceptions.duplicate'));
        }

        $this->wallet = $this->wallets->create([
            'crcy' => $crcy,
        ]);

        if (! is_null($total)) {
            $this->createTransaction($total)->withNote(trans('wallets::wallets.initial_income'));
        }

        return $this->wallet;
    }

    /**
     * Finds wallet by given currency or returns all wallets if null.
     *
     * @param string|null $crcy Currency name in XYZ format
     *
     * @return \Illuminate\Database\Eloquent\Collection|\KluseG\LaravelWallets\Models\Wallet
     *
     * @throws \KluseG\LaravelWallets\Exceptions\WalletNotFoundException
     */
    public function get(string $crcy = null)
    {
        $this->checkContext();

        if (is_null($crcy)) {
            return $this->wallets->get();
        }

        if (! $this->walletExists($crcy)) {
            throw new WalletNotFoundException(trans('wallets::exceptions.not_found_for', ['for' => $crcy]));
        }

        return $this->wallets->where('crcy', $crcy)->firstOrFail();
    }

    /**
     * Returns or calculates Wallet's total.
     *
     * @param \Carbon\Carbon|null $since  Date from
     * @param bool                $pretty Determines if output should be pretty printed
     *
     * @return string|float
     */
    public function getTotal(Carbon $since = null, bool $pretty = false)
    {
        $this->checkWallet();

        if (is_null($since)) {
            $total = $this->wallet->total;
        } else {
            $total = 0.0;

            foreach ($this->wallet->transactions()->where('created_at', '>=', $since)->get() as $t) {
                if ($t->income) {
                    $total += $t->amount;
                } else {
                    $total -= $t->amount;
                }
            }
        }

        if ($pretty) {
            return number_format($total, 2);
        }

        return $total;
    }

    /**
     * Calculates Wallet's total within given date range.
     *
     * @param \Carbon\Carbon $from   Date start
     * @param \Carbon\Carbon $to     Date end
     * @param bool           $pretty Determines if output should be pretty printed
     *
     * @return string|float
     */
    public function getTotalBetween(Carbon $from, Carbon $to, bool $pretty = false)
    {
        $this->checkWallet();

        $total = 0.0;

        foreach ($this->wallet->transactions()->where('created_at', '>=', $from)->where('created_at', '<=', $to)->get() as $t) {
            if ($t->income) {
                $total += $t->amount;
            } else {
                $total -= $t->amount;
            }
        }

        if ($pretty) {
            return number_format($total, 2);
        }

        return $total;
    }

    /**
     * Saves wallet income.
     *
     * @param   float $amount  Income amount
     *
     * @return  \KluseG\LaravelWallets\Models\WalletTransaction
     */
    public function income(float $amount) : WalletTransaction
    {
        return $this->createTransaction($amount);
    }

    /**
     * Sets currently used Wallet.
     *
     * @param   string $crcy  Currency name in XYZ format
     *
     * @return  self
     *
     * @throws \KluseG\LaravelWallets\Exceptions\WalletNotFoundException
     */
    public function on(string $crcy) : self
    {
        $this->checkContext();

        if (! $this->walletExists($crcy)) {
            throw new WalletNotFoundException(trans('wallets::exceptions.not_found_for', ['for' => $crcy]));
        }

        $this->wallet = $this->wallets->where('crcy', $crcy)->firstOrFail();

        return $this;
    }

    /**
     * Saves wallet outcome.
     *
     * @param   float $amount  Outcome amount
     *
     * @return  \KluseG\LaravelWallets\Models\WalletTransaction
     */
    public function outcome(float $amount) : WalletTransaction
    {
        return $this->createTransaction($amount, false);
    }

    /**
     * Sets currently used walletable instance.
     *
     * @param   Model $context  Walletable
     *
     * @return  self
     */
    public function setContext(Model $context) : self
    {
        $this->ctx = $context;

        return $this->setRelation();
    }

    /**
     * Checks if currently set Walletable is valid.
     *
     * @return  bool
     *
     * @throws \KluseG\LaravelWallets\Exceptions\InvalidContextException
     */
    protected function checkContext() : bool
    {
        if (! isset($this->ctx) || empty($this->ctx) || ! ($this->ctx instanceof Model)) {
            throw new InvalidContextException(trans('wallets::exceptions.context', ['context' => Model::class]));
        } elseif (! isset($this->wallets) || empty($this->wallets)) {
            return $this->setRelation()->checkContext();
        }

        return true;
    }

    /**
     * Check if currently set Wallet instance is valid.
     *
     * @return  bool
     *
     * @throws \KluseG\LaravelWallets\Exceptions\WalletNotFoundException
     */
    protected function checkWallet() : bool
    {
        if (! isset($this->wallet) || empty($this->wallet) || ! ($this->wallet instanceof Wallet)) {
            throw new WalletNotFoundException(trans('wallets::exceptions.not_found'));
        }

        return true;
    }

    /**
     * Creates new transaction.
     *
     * @param float $amount Transaction amount
     * @param bool  $income Determines transaction type: income or outcome
     *
     * @return \KluseG\LaravelWallets\Models\WalletTransaction
     *
     * @throws \KluseG\LaravelWallets\Exceptions\WalletEmptyException
     */
    protected function createTransaction(float $amount, bool $income = true) : WalletTransaction
    {
        $this->checkWallet();

        if (! $income && ! $this->isTransactionAllowed($amount)) {
            throw new WalletEmptyException(trans('wallets::exceptions.empty'));
        }

        return $this->wallet->transactions()->create([
            'amount' => $amount,
            'income' => $income,
        ]);
    }

    /**
     * Checks if transaction is allowed.
     *
     * @param float $amount Transaction amount
     *
     * @return bool
     */
    protected function isTransactionAllowed(float $amount) : bool
    {
        if (config('wallets.allow_credit', true)) {
            return true;
        }

        return ($this->wallet->total - $amount) >= 0;
    }

    /**
     * Sets MorphMany relation for setted context.
     *
     * @return  self
     */
    protected function setRelation() : self
    {
        $this->wallets = $this->ctx->morphMany(Wallet::class, 'walletable');

        return $this;
    }

    /**
     * Checks if wallet with given currency exists.
     *
     * @param string $crcy Currency name in XYZ format
     *
     * @return bool
     */
    protected function walletExists(string $crcy) : bool
    {
        return $this->wallets->where('crcy', $crcy)->exists();
    }
}
