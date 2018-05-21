<?php

namespace JEL\RealCasino\Repositories;

use JEL\RealCasino\Entities\Casino;
use JEL\RealCasino\Entities\Casinos;
use JEL\RealCasino\Entities\CasinoUsers;
use JEL\RealCasino\Entities\RealCasino;
use JEL\Users\Entities\User;
use JEL\Users\Contracts\Users;
use Juegaenlinea\Walletwrapper\Enums\TransactionType;

/**
 * Class RealCasino
 *
 * @package JEL\RealCasino\Repositories
 * @author Carol Mirabal
 */
class RealCasinoRepo
{
    /**
     * @param $transaction
     * @param $user
     * @return static
     */
    public function findTransaction($transaction, $user, $type)
    {
        $transaction = RealCasino::where('transaction', $transaction)
            ->where('user', $user)
            ->where('transactiontype', $type)
            ->first();
        return $transaction;
    }

    /**
     *
     * @param $transaction
     * @param $user
     * @param $amount
     * @param $status
     * @param $operator
     * @param $transactionwallet
     * @return mixed
     */
    public function transactionUpdate($transaction, $user, $amount, $status, $operator, $transactionwallet)
    {
        $query = RealCasino::where('transaction', $transaction)
            ->where('user', $user)
            ->update([
                'amount' => $amount,
                'transactionwallet' => $transactionwallet,
                'status' => $status,
                'operator' => $operator
            ]);
        return $query;
    }

    /**
     * @param $currency
     * @param $whitelabel
     * @return mixed
     */
    public function getAllTransactions($currency, $whitelabel, $timezone, $startDate, $endDate, $casino)
    {
        $query = "SELECT rctransactions.transaction, transactiontype,
                  transactionwallet,rctransactions.currency,amount, rctransactions.user,rctransactions.created_at,rctransactions.updated_at,rctransactions.status, users.username,
                  CASE WHEN rctransactions.balance IS NULL THEN 0 ELSE rctransactions.balance END AS balance
                  from rctransactions
LEFT JOIN users on users.id = rctransactions.user
 left join rccasinousers on rccasinousers.user = rctransactions.user
        left join rccasino on rccasino.id = rccasinousers.casino
where rccasino.casino = ?
    and rccasino.whitelabel = ?
    and rctransactions.currency = ?
        and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN ? AND ? ";


        return \DB::select($query, [$casino, $whitelabel, $currency, $startDate, $endDate]);
    }

    /**
     * @param $currency
     * @param $whitelabel
     * @param $timezone
     * @param $startDate
     * @param $endDate
     * @param $casino
     * @return mixed
     */
    public function getDepositTransactions($currency, $whitelabel, $timezone, $startDate, $endDate, $casino)
    {
        $query = RealCasino::select('rctransactions.transaction', 'transactiontype', 'transactionwallet', 'rctransactions.currency', 'amount', 'rctransactions.user', 'rctransactions.created_at', 'rctransactions.updated_at', 'rctransactions.status', 'username')
            ->leftjoin('users', 'users.id', '=', 'rctransactions.operator')
            ->leftJoin('rccasinousers', 'rctransactions.user', '=', 'rccasinousers.user')
            ->leftJoin('rccasino', 'rccasinousers.casino', '=', 'rccasino.id')
            ->where('rccasino.casino', '=', $casino)
            ->where('rctransactions.currency', $currency)
            ->where('rctransactions.whitelabel', $whitelabel)
            ->where('rctransactions.transactiontype', TransactionType::$credit)
            ->whereBetween(\DB::raw("(rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '" . $timezone . "')::DATE"), [$startDate, $endDate])
            ->orderBy('rctransactions.updated_at', 'desc')
            ->get();
        return $query;
    }

    /**
     * @param $currency
     * @param $whitelabel
     * @param $timezone
     * @param $startDate
     * @param $endDate
     * @param $casino
     * @return mixed
     */
    public function getWithdrawalTransactions($currency, $whitelabel, $timezone, $startDate, $endDate, $casino)
    {
        $query = RealCasino::select('rctransactions.transaction', 'transactiontype', 'transactionwallet', 'rctransactions.currency', 'amount', 'rctransactions.user', 'rctransactions.created_at', 'rctransactions.updated_at', 'rctransactions.status', 'username')
            ->leftJoin('users', 'users.id', '=', 'rctransactions.operator')
            ->leftJoin('rccasinousers', 'rctransactions.user', '=', 'rccasinousers.user')
            ->leftJoin('rccasino', 'rccasinousers.casino', '=', 'rccasino.id')
            ->where('rccasino.casino', '=', $casino)
            ->where('rctransactions.currency', $currency)
            ->where('rctransactions.whitelabel', $whitelabel)
            ->where('rctransactions.transactiontype', TransactionType::$debit)
            ->whereBetween(\DB::raw("(rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '" . $timezone . "')::DATE"), [$startDate, $endDate])
            ->orderBy('rctransactions.updated_at', 'desc')
            ->get();
        return $query;
    }

    /**
     * @param $timezone
     * @param $startDate
     * @param $endDate
     * @param $whitelabel
     * @param $currency
     * @return mixed
     */
    public function totalTransactions($timezone, $startDate, $endDate, $whitelabel, $currency, $casino)
    {
        $query = "SELECT DISTINCT
	SUM (creditapproved) as creditapproved,
	SUM (creditpending) as creditpending,
	debitapproved,
	debitpending
FROM
	(
		SELECT
			CASE
		WHEN subcred.status = 10 THEN
			SUM (subcred.amountt)
		ELSE
			0
		END AS creditapproved,
		CASE
	WHEN subcred.status = 11 THEN
		COUNT (*)
	ELSE
		0
	END AS creditpending,
	subcred.casino
FROM
	(
		SELECT
			CASE
		WHEN amount IS NULL THEN
			0
		ELSE
			amount
		END AS amountt,
		rccasino.casino,
		rctransactions.status,
		rctransactions.id
	FROM
		rctransactions
	LEFT JOIN rccasinousers ON rccasinousers. USER = rctransactions.user
	LEFT JOIN rccasino ON rccasinousers.casino = rccasino. ID
	WHERE
		rccasino.casino = ?
	AND rctransactions.transactiontype = 1
	AND (
		rctransactions.created_at :: TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone'
	) :: DATE BETWEEN ?
	AND ?
	AND rctransactions.status <> 3
	AND rctransactions.currency = ?
	GROUP BY
		rccasino.casino,
		rctransactions. ID
	) AS subcred
GROUP BY
	subcred.amountt,
	subcred.status,
	subcred.casino
	) AS credit
LEFT JOIN (
	SELECT
		CASE
	WHEN subdeb.status = 10 THEN
		SUM (subdeb.amountt)
	ELSE
		0
	END AS debitapproved,
	CASE
WHEN subdeb.status = 11 THEN
	COUNT (*)
ELSE
	0
END AS debitpending,
 subdeb.casino
FROM
	(
		SELECT
			CASE
		WHEN amount IS NULL THEN
			0
		ELSE
			amount
		END AS amountt,
		rccasino.casino,
		rctransactions.status,
		rctransactions.id
	FROM
		rctransactions
	LEFT JOIN rccasinousers ON rccasinousers. USER = rctransactions.user
	LEFT JOIN rccasino ON rccasinousers.casino = rccasino. ID
	WHERE
		rccasino.casino = ?
	AND rctransactions.transactiontype = 2
	AND (
		rctransactions.created_at :: TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone'
	) :: DATE BETWEEN ?
	AND ?
	AND rctransactions.status <> 3
	AND rctransactions.currency = ?
	GROUP BY
		rccasino.casino,
		rctransactions. ID
	) AS subdeb
GROUP BY
	subdeb.amountt,
	subdeb.status,
	subdeb.casino
) AS debit ON debit.casino = credit.casino
GROUP BY
	debitpending,
	debitapproved";

        return \DB::select($query, [$casino, $startDate, $endDate, $currency, $casino, $startDate, $endDate, $currency]);
    }

    /**
     * @param $whitelabel
     * @return mixed
     */
    public function getUsers($whitelabel, $casino)
    {
        $users = User::select('users.id', 'username', 'whitelabel')
            ->leftJoin('rccasinousers', 'users.id', '=', 'rccasinousers.user')
            ->where('users.whitelabel', '=', $whitelabel)
            ->where('rccasinousers.casino', '=', $casino)
            ->orderBy('users.username', 'ASC')
            ->get();
        return $users;
    }


    /**
     * @param $whitelabel
     * @return mixed
     */
    public function getKey($whitelabel, $user)
    {
        $users = User::select('users.id', 'username', 'whitelabel', 'key')
            ->where('users.whitelabel', '=', $whitelabel)
            ->where('users.id', '=', $user)
            ->first();

        return $users;
    }

    /**
     * @param $whitelabel
     * @param $user
     * @return mixed
     */
    public function findUser($whitelabel, $user)
    {
        $users = User::select('users.id', 'username', 'locked', 'created_at')
            ->where('users.whitelabel', '=', $whitelabel)
            ->where('users.id', '=', $user)
            ->first();

        return $users;
    }

    /**
     * @param $whitelabel
     * @param $casino
     * @return mixed
     */
    public function findCasino($whitelabel, $user)
    {
        $users = Casino::select('rccasino.id', 'rccasino.casino', 'users.username', 'rccasino.name', 'rccasino.description', 'rccasino.created_at', 'rccasino.operationlimit', 'rccasino.businessvalue', 'rccasino.businesstype')
            ->leftJoin('users', 'users.id', '=', 'rccasino.casino')
            ->leftJoin('rccasinousers', 'rccasinousers.casino', '=', 'rccasino.id')
            ->where('rccasino.whitelabel', '=', $whitelabel)
            ->where('rccasinousers.user', '=', $user)
            ->first();

        return $users;
    }


    /**
     * @param $whitelabel
     * @param $casino
     * @return mixed
     */
    public function findCasinoById($whitelabel, $casino)
    {
        $users = Casino::select('rccasino.id', 'users.username', 'rccasino.name', 'rccasino.description', 'rccasino.created_at', 'rccasino.operationlimit', 'rccasino.casino')
            ->leftJoin('users', 'users.id', '=', 'rccasino.casino')
            ->where('rccasino.whitelabel', '=', $whitelabel)
            ->where('rccasino.id', '=', $casino)
            ->first();

        return $users;
    }

    /**
     * @param $whitelabel
     * @param $casino
     * @return mixed
     */
    public function findCasinoByUserCasino($whitelabel, $casino)
    {
        $users = Casino::select('rccasino.id', 'users.username', 'rccasino.name', 'rccasino.description', 'rccasino.created_at')
            ->leftJoin('users', 'users.id', '=', 'rccasino.casino')
            ->where('rccasino.whitelabel', '=', $whitelabel)
            ->where('rccasino.casino', '=', $casino)
            ->first();

        return $users;
    }

    /**
     * @param $casino
     * @return mixed
     */
    public function getUsersCasino($casino)
    {
        $users = User::select('users.id', 'username', 'locked', 'created_at')
            ->leftJoin('rccasinousers', 'users.id', '=', 'rccasinousers.user')
            ->where('rccasinousers.casino', '=', $casino)
            ->get();

        return $users;
    }

    /**
     * Delete user casino
     *
     * @param $user
     */
    public function deleteUsersCasino($casino, $user)
    {
        $user = CasinoUsers::where('casino', $casino)->where('user', $user);
        $user->delete();
    }

    /**
     * Delete user casino
     *
     * @param $casino
     * @param $user
     * @return CasinoUsers
     */
    public function addUsersCasino($casino, $user)
    {
        $rc = \DB::table('rccasinousers')->insert(
            ['casino' => $casino, 'user' => $user]);

        return $rc;
    }

    /**
     * @param $user
     * @return mixed
     * @internal param $casino
     */
    public function getUserById($user)
    {
        $users = User::select('users.id', 'username', 'locked')
            ->leftJoin('rccasinousers', 'users.id', '=', 'rccasinousers.user')
            ->where('rccasinousers.user', '=', $user)
            ->first();

        return $users;
    }

    /**
     * @param $user
     * @return mixed
     * @internal param $casino
     */
    public function getCasinos($user)
    {
        $users = \DB::table('rccasino')
            ->select('rccasino.name', 'rccasino.description', 'rccasino.casino', 'rccasino.id')
            ->where('rccasino.owner', '=', $user)
            ->get();

        return $users;
    }

    /**
     * @param $casino
     * @param $owner
     * @return mixed
     */
    public function findCasinosSelect($casino, $owner)
    {
        $casinos = \DB::table('rccasino')
            ->select('rccasino.name', 'rccasino.description', 'rccasino.casino', 'rccasino.id')
            ->where('rccasino.owner', '=', $owner)
            ->where('rccasino.name', 'like', strtolower($casino) . '%')
            ->get();

        return $casinos;
    }

    /**
     * @param $casino
     * @param $description
     * @param $casinoname
     * @param $whiteLabel
     * @param $owner
     * @return mixed
     */
    public function createCasino($casino, $description, $casinoname, $whiteLabel, $owner, $value, $type, $limit)
    {
        $rc = new Casino();
        $rc->casino = $casino;
        $rc->name = $casinoname;
        $rc->description = $description;
        $rc->whitelabel = $whiteLabel;
        $rc->owner = $owner;
        $rc->businessvalue = $value;
        $rc->businesstype = $type;
        $rc->operationlimit = $limit;
        $rc->save();

        return $rc;
    }

    /**
     * @param $owner
     * @param $timezone
     * @param $startDate
     * @param $endDate
     * @internal param $casino
     */
    public function getProfitCasinos($owner, $timezone, $startDate, $endDate)
    {
        $query = "select pc.id, pc.description, pc.name, sum(pc.won) as won, sum(pc.played) as played from
 (select * from (select sub.id, sub.name, sub.description, sum(sub.played) as played, sum(sub.won) as won from
      (SELECT
      rccasino.id,
      rccasino.name,
      rccasino.description,
        case when transactiontype=1 then sum(amount) else 0 end as played,
        case when transactiontype=2 then sum(amount) else 0 end as won from rctransactions
        left join rccasinousers on rccasinousers.user = rctransactions.user
        left join rccasino on rccasino.id = rccasinousers.casino
        where rccasino.owner = ?
        and rctransactions.status = 10
        and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN ? AND ?
        GROUP BY rccasino.name, rccasino.description, transactiontype, rccasino.id) sub
        GROUP BY sub.name, sub.description, sub.id)as dt
        UNION
        SELECT rccasino.id,rccasino.name, rccasino.description, 0 as played, 0 as won from rccasino where rccasino.owner = ?) as pc
        GROUP by pc.id, pc.name, pc.description";


        return \DB::select($query, [$owner, $startDate, $endDate, $owner]);
    }

    /**
     * @param $owner
     * @param $timezone
     * @param $startDate
     * @param $endDate
     * @internal param $casino
     */
    public function getTotalProfitCasinos($owner, $timezone, $startDate, $endDate)
    {
        $query = "select sum(sub.deposit) as deposit, sum(sub.withdrawal) as withdrawal from
      (SELECT
        case when transactiontype=1 then sum(amount) else 0 end as deposit,
        case when transactiontype=2 then sum(amount) else 0 end as withdrawal from rctransactions
        left join rccasinousers on rccasinousers.user = rctransactions.user
        left join rccasino on rccasino.id = rccasinousers.casino
        where rccasino.owner = ?
        and rctransactions.status = 10
        and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN ? AND ?
        GROUP BY  transactiontype) sub";


        return \DB::select($query, [$owner, $startDate, $endDate]);
    }

    /**
     * Get profit users
     *
     * @param $casino
     * @param $timezone
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getProfitUsersCasino($casino, $timezone, $startDate, $endDate)
    {
        $query = "SELECT des.user as id, des.username, sum(des.deposit) as deposit, sum(des.withdrawal) as withdrawal  from (SELECT * from(select depos.user,
        depos.username,
        CASE WHEN depos.deposit IS NULL THEN 0 ELSE depos.deposit END AS deposit,
        CASE WHEN withdra.withdrawal IS NULL THEN 0 ELSE withdra.withdrawal END AS withdrawal from
        (SELECT  rctransactions.user,
              users.username, sum(amount) as deposit
        from rctransactions
             left join rccasinousers on rccasinousers.user = rctransactions.user
                LEFT JOIN users on users.id = rccasinousers.user
                left join rccasino on rccasino.id = rccasinousers.casino
                where rccasino.casino = ?
                and rctransactions.status = 10
        and rctransactions.transactiontype=1
        and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN ? AND ?
        GROUP BY users.username, rctransactions.user) as depos
        LEFT JOIN (
        SELECT rctransactions.user,
        sum(amount) as withdrawal
        from rctransactions
             left join rccasinousers on rccasinousers.user = rctransactions.user
                LEFT JOIN users on users.id = rccasinousers.user
                left join rccasino on rccasino.id = rccasinousers.casino
                where rccasino.casino = ?
                and rctransactions.status = 10
        and rctransactions.transactiontype=2
        and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN ? AND ?
        GROUP BY rctransactions.user
        ) as withdra on depos.user = withdra.user
        GROUP BY depos.username, depos.user, depos.deposit, withdra.withdrawal ) as dru
        union select rccasinousers.user, users.username, 0 as deposit, 0 as withdrawal  from rccasinousers
        LEFT JOIN users on rccasinousers.user=users.id
        left join rccasino on rccasino.id = rccasinousers.casino
        where rccasino.casino = ? )as des
        GROUP BY des.user, des.username, des.deposit, des.withdrawal
        order by des.deposit DESC";

        return \DB::select($query, [$casino, $startDate, $endDate, $casino, $startDate, $endDate, $casino]);
    }


    /**
     * @param $casino
     * @param $timezone
     * @param $startDate
     * @param $endDate
     * @internal param $casino
     */
    public function getTotalProfitUsersCasino($casino, $timezone, $startDate, $endDate)
    {
        $query = "select sum(sub.deposit) as deposit, sum(sub.withdrawal) as withdrawal from
      (SELECT
        case when transactiontype=1 then sum(amount) else 0 end as deposit,
        case when transactiontype=2 then sum(amount) else 0 end as withdrawal from rctransactions
        left join rccasinousers on rccasinousers.user = rctransactions.user
        left join rccasino on rccasino.id = rccasinousers.casino
        where rccasino.casino = ?
        and rctransactions.status = 10
        and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN ? AND ?
        GROUP BY  transactiontype) sub";


        return \DB::select($query, [$casino, $startDate, $endDate]);
    }


    /**
     * @param $whitelabel
     * @param $casino
     * @return mixed
     */
    public function getOnlineUserCasino($whitelabel, $casino)
    {
        $count = \DB::select("select count(*)from (select distinct sessionsjel.user from sessionsjel
left join sessions on sessions.id = sessionsjel.id
left join users on users.id = sessionsjel.user
   left join rccasinousers on rccasinousers.user = sessionsjel.user
 left join rccasino on rccasino.id = rccasinousers.casino
        where rccasino.casino = ?
           and users.whitelabel = ?) as online", [$casino, $whitelabel]);
        return $count[0]->count;
    }

    /**
     * @param $timezone
     * @param $casino
     * @param $whitelabel
     * @param $startdate
     * @param $enddate
     * @param $currency
     * @return mixed
     */
    public function graphicDashboardCasino($timezone, $casino, $whitelabel, $startdate, $enddate, $currency)
    {
        $rc = \DB::select("SELECT ct.m as month,sum(ct.credit) as credit, dt.debit as debit, ct.year_val  from
            (select  EXTRACT (MONTH FROM rctransactions.created_at:: TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone'
  ) as m, EXTRACT (YEAR FROM rctransactions.created_at:: TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone'
  ) as year_val ,sum(rctransactions.amount)::INTEGER as credit
            from rctransactions
            inner join users on users.id = rctransactions.user
 left join rccasinousers on rccasinousers.user = rctransactions.user
        left join rccasino on rccasino.id = rccasinousers.casino
        where rccasino.casino = $casino
            AND rctransactions.status = 10
            and users.whitelabel = $whitelabel
AND rctransactions.transactiontype = 1
and users.currency= '$currency'
and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN '$startdate' and '$enddate'
						GROUP BY rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone', rctransactions.amount) ct
           left JOIN
           (SELECT sum(debit) as debit, du.m as m from (select EXTRACT (MONTH FROM rctransactions.created_at:: TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone'
  ) as m, sum(rctransactions.amount)::INTEGER as debit
            from rctransactions
            inner join users on users.id = rctransactions.user
left join rccasinousers on rccasinousers.user = rctransactions.user
        left join rccasino on rccasino.id = rccasinousers.casino
        where rccasino.casino = $casino
            AND rctransactions.status = 10
            and users.whitelabel = $whitelabel
and users.currency= '$currency'
AND rctransactions.transactiontype = 2
and (rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone')::DATE BETWEEN '$startdate' and '$enddate'
						GROUP BY rctransactions.created_at::TIMESTAMP WITH TIME ZONE AT TIME ZONE '$timezone') du
GROUP BY m) dt
on dt.m = ct.m
GROUP BY ct.m, dt.debit, ct.year_val
ORDER BY year_val");

        return $rc;
    }

    /**
     * Edit Casino from details casino
     *
     * @param $user
     * @param $field
     * @param $value
     * @return mixed
     */
    public function updateCasino($casino, $field, $value)
    {
        return \DB::table('rccasino')->where('id', $casino)->update([$field => $value]);
    }

    /**
     * @param $casino
     * @param $limit
     * @return mixed
     */
    public function updateLimit($casino, $limit)
    {
        return \DB::table('rccasino')->where('id', $casino)->update(['operationlimit' => $limit]);
    }

    /**
     * @param $casino
     * @param $description
     * @return mixed
     */
    public function updateDescription($casino, $description)
    {
        return \DB::table('rccasino')->where('id', $casino)->update(['description' => $description]);
    }

    /**
     * @param $user
     * @return mixed
     */
    public function findCasinoByUser($user)
    {
        $rc = \DB::table('rccasinousers')
            ->select('rccasino.casino')
            ->leftJoin('rccasino', 'rccasino.id', '=', 'rccasinousers.casino')
            ->where('rccasinousers.user', $user)
            ->first();

        return $rc;
    }

}
