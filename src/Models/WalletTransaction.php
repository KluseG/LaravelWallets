<?php

namespace KluseG\LaravelWallets\Models;

use Illuminate\Database\Eloquent\Model;
use KluseG\LaravelWallets\Events\WalletTransactionCreated as TransactionCreated;

class WalletTransaction extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'wallet_id' => 'integer',
        'amount' => 'double',
        'income' => 'boolean',
        'note' => 'string',
        'details' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TransactionCreated::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'details', 'income', 'note',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallet_transactions';

    /**
     * Get the wallet for the transaction.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Updates current model with details array.
     *
     * @param array $details Details to save
     *
     * @return self
     */
    public function withDetails(array $details)
    {
        $this->update([
            'details' => $details,
        ]);

        return $this;
    }

    /**
     * Updates current model with note.
     *
     * @param string $note Transaction note
     *
     * @return self
     */
    public function withNote(string $note)
    {
        $this->update([
            'note' => $note,
        ]);

        return $this;
    }
}
