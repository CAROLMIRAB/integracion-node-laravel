<?php

namespace JEL\RealCasino\Repositories;

use JEL\RealCasino\Entities\RealCasino;


/**
 * Class RealCasino
 *
 * @package JEL\Core\Repositories
 * @author  Carol Mirabal
 */
class RealCasinoRepo
{
    /**
     * Create transaction real casino
     *
     * @param $transaction
     * @param $status
     * @param $transactiontype
     * @param $user
     * @param $whitelabel
     * @param $currency
     * @param $balance
     * @return RealCasino
     */
    public function requestTransaction($transaction, $status, $transactiontype, $user, $whitelabel, $currency, $balance)
    {
        $rc = new RealCasino();
        $rc->transaction = $transaction;
        $rc->status = $status;
        $rc->transactiontype = $transactiontype;
        $rc->user = $user;
        $rc->currency = $currency;
        $rc->whitelabel = $whitelabel;
        $rc->balance = $balance;
        $rc->save();
        return $rc;
    }

    public function findTransactionPending($user, $status)
    {
        $rc = RealCasino::select('id')
            ->where('user', $user)
            ->where('status', $status)
            ->get();

        return $rc;
    }

    /**
     * @param $user
     * @return mixed
     */
    public function findCasino($user)
    {
        $rc = \DB::table('rccasinousers')
            ->select('rccasino.casino')
            ->leftJoin('rccasino', 'rccasino.id', '=', 'rccasinousers.casino')
            ->where('rccasinousers.user', $user)
            ->first();

        return $rc;
    }

}
