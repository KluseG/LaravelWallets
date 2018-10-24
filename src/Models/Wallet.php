<?php

namespace KluseG\LaravelWallets\Models;

use Illuminate\Database\Eloquent\Model;
use KluseG\LaravelWallets\Events\WalletUpdated;

class Wallet extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'walletable_id' => 'integer',
        'walletable_type' => 'string',
        'total' => 'double',
        'crcy' => 'string',
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
        'updated' => WalletUpdated::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'crcy', 'total',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wallets';

    /**
     * Transforms and sets "crcy" attribute.
     *
     * @return void
     */
    public function setCrcyAttribute($value)
    {
        $this->attributes['crcy'] = strtoupper($value);
    }

    /**
     * Get the transactions for the wallet.
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }

    /**
     * Get all of the owning walletable models.
     */
    public function walletable()
    {
        return $this->morphTo();
    }
}
