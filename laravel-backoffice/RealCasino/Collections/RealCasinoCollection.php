<?php

namespace JEL\RealCasino\Collections;

use Carbon\Carbon;
use JEL\Core\Enums\Status;
use JEL\External\Enums\Utils;

/**
 * Obtain the formatted data to send to the view
 * Class CreditTransactionsCollection
 * @package JEL\Accounting\Collections
 * @author  Carol Mirabal
 */
class RealCasinoCollection
{

    /**
     * Datatable ServerSide data get credit transaction
     *
     * @param $transactions
     * @return mixed
     */
    public function allTransactions($transactions)
    {
        $datatable = collect();
        $currency = \Session::get('currency');

        foreach ($transactions as $transaction) {
            $transaction->id = $transaction->transaction;
            $transaction->localcurrency = $transaction->currency;
            $generated_date = new Carbon($transaction->created_at);
            $generated_date->setTimezone(\Session::get('timezone'));
            $transaction->generated_date = $generated_date->format('d-m-Y H:i:s');
            $updated_date = new Carbon($transaction->updated_at);
            $updated_date->setTimezone(\Session::get('timezone'));
            $transaction->updated_date = $updated_date->format('d-m-Y H:i:s');

            switch ($transaction->status) {
                case Status::$pending: {
                    switch ($transaction->transactiontype) {
                        case 1: {
                            $transaction->actions = "<button type='button' class='btn green btn-xs open-modal-drc' data-toggle='modal' data-target='#modalDepositDRC' data-transaction='" . $transaction->transaction . "' data-user='" . $transaction->user . "' data-transactiontype='" . $transaction->transactiontype . "' data-username='" . $transaction->username . "' data-balance='" . $transaction->balance . "'>" . __('Aprobar') . "</button>";
                            break;
                        }
                        case 2 : {
                            $transaction->actions = "<button type='button' class='btn green btn-xs open-modal-wrc' data-toggle='modal' data-target='#modalDepositWRC' data-transaction='" . $transaction->transaction . "' data-user='" . $transaction->user . "' data-transactiontype='" . $transaction->transactiontype . "' data-username='" . $transaction->username . "' data-balance='" . $transaction->balance . "'>" . __('Aprobar') . "</button>";
                            break;
                        }
                    }
                    $transaction->actions .= "<button type='button' class='btn red btn-xs mdrc_rejected reload' data-route='" . route('realcasino.cancelledtransaction') . "' data-loading-text='Loading...' data-transaction='" . $transaction->transaction . "' data-user='" . $transaction->user . "' data-transactiontype='" . $transaction->transactiontype . "'>" . __('Rechazar') . "</button>";
                    $transaction->statusf = "<span class='label label-sm bg-blue-steel'>" . __('Pendiente') . "</span>";
                    break;
                }
                case Status::$cancelled : {
                    $transaction->actions = "";
                    $transaction->statusf = "<span class='label label-sm bg-red-thunderbird'>" . __('Rechazado') . "</span>";
                    break;
                }
                case Status::$approved: {
                    $transaction->actions = "";
                    $transaction->statusf = "<span class='label label-sm bg-green-jungle'>" . __('Aprobado') . "</span>";
                    break;
                }
            }

            switch ($transaction->transactiontype) {
                case 1: {
                    $transaction->type = __("Deposito");
                    break;
                }
                case 2 : {
                    $transaction->type = __("Retiro");
                    break;
                }
            }

            $transaction->username = sprintf(
                '<a href="%s" class="btn btn-xs default">%s</a>',
                route('user-details', [$transaction->user]),
                $transaction->username
            );


            $datatable->push([
                'generated' => $transaction->generated_date,
                'updated' => $transaction->updated_date,
                'id' => $transaction->transaction,
                'type' => $transaction->type,
                'username' => $transaction->username,
                'amount' => $transaction->amount,
                'status' => $transaction->statusf,
                'actions' => $transaction->actions,
                'decimals' => intval($currency['decimals']),
                'decimalseparator' => $currency['decimalseparator'],
                'thousandseparator' => $currency['thousandseparator']
            ]);
        }

        return $datatable;
    }

    /**
     * Datatable ServerSide data get credit transaction
     *
     * @param $transactions
     * @return mixed
     */
    public function reportTransactions($transactions)
    {
        $datatable = collect();
        $currency = \Session::get('currency');
        foreach ($transactions as $transaction) {
            $transaction->id = $transaction->transaction;
            $transaction->localcurrency = $transaction->currency;
            $generated_date = new Carbon($transaction->created_at);
            $generated_date->setTimezone(\Session::get('timezone'));
            $transaction->generated_date = $generated_date->format('d-m-Y H:i:s');
            $updated_date = new Carbon($transaction->updated_at);
            $updated_date->setTimezone(\Session::get('timezone'));
            $transaction->updated_date = $updated_date->format('d-m-Y H:i:s');

            switch ($transaction->status) {
                case Status::$pending: {
                    $transaction->statusf = "<span class='label label-sm bg-blue-steel'>" . __('Pendiente') . "</span>";
                    break;
                }
                case Status::$cancelled : {
                    $transaction->statusf = "<span class='label label-sm bg-red-thunderbird'>" . __('Rechazado') . "</span>";
                    break;
                }
                case Status::$approved: {
                    $transaction->statusf = "<span class='label label-sm bg-green-jungle'>" . __('Aprobado') . "</span>";
                    break;
                }
            }

            switch ($transaction->transactiontype) {
                case 1: {
                    $transaction->type = __("Deposito");
                    break;
                }
                case 2 : {
                    $transaction->type = __("Retiro");
                    break;
                }
            }

            $transaction->username = sprintf(
                '<a href="%s" class="btn btn-xs default">%s</a>',
                route('user-details', [$transaction->user]),
                $transaction->username
            );


            $datatable->push([
                'generated' => $transaction->generated_date,
                'updated' => $transaction->updated_date,
                'id' => $transaction->transaction,
                'operator' => $transaction->username,
                'amount' => $transaction->amount,
                'status' => $transaction->statusf,
                'decimals' => intval($currency['decimals']),
                'decimalseparator' => $currency['decimalseparator'],
                'thousandseparator' => $currency['thousandseparator']
            ]);
        }

        return $datatable;
    }

    /**
     * Totals process all transactions
     *
     * @param $transactions
     * @return array
     */
    public function totalsTransactions($transactions)
    {
        $currency = \Session::get('currency');
        $result = [];
        if (empty($transactions) || is_null($transactions)) {
            $result = [
                'credit_approved' => 0,
                'credit_pending' => 0,
                'debit_approved' => 0,
                'debit_pending' => 0
            ];
        } else {
            $debitapproved = 0;
            $debitpending = 0;
            foreach ($transactions as $items) {
                $debitapproved += $items->debitapproved;
                $debitpending += $items->debitpending;
                $result = [
                    'credit_approved' => $currency['symbol'] . " " . number_format($items->creditapproved, intval($currency['decimals']), $currency['decimalseparator'], $currency['thousandseparator']),
                    'credit_pending' => $items->creditpending,
                    'debit_approved' => $currency['symbol'] . " " . number_format($debitapproved, intval($currency['decimals']), $currency['decimalseparator'], $currency['thousandseparator']),
                    'debit_pending' => $debitpending
                ];
            }
        }
        return $result;

    }

    /**
     * Panel administration users
     *
     * @param $users
     * @return \Illuminate\Support\Collection
     */
    public function panelAdministration($users)
    {
        $datatable = collect();
        foreach ($users as $user) {

            $user->username = sprintf(
                '<a href="%s" class="btn btn-xs default">%s</a>',
                route('user-details', [$user->id]),
                $user->username
            );

            $datatable->push([
                'username' => $user->username,
                'route' => route('realcasino.blokeduser'),
                'user' => $user->id,
                'whitelabel' => $user->whitelabel,
                'routelogout' => route('realcasino.logoutuser'),
                'routelogin' => route('realcasino.loginuser'),
            ]);
        }
        return $datatable;
    }

    /**
     * Users playbox "Casinos"
     *
     * @param $users
     * @return \Illuminate\Support\Collection
     */
    public function usersPlaybox($casinos)
    {
        $userData = collect();
        foreach ($casinos as $casino) {

            $userData->push([
                "name" => $casino->name,
                "description" => $casino->description,
                "actions" => "<a href='" . route('realcasino.casinodetails', $casino->id) . "' class='btn default btn-xs green-stripe'><i class='fa fa-pencil'></i> " . __('Editar') . "</a>"
            ]);

        }
        return $userData;
    }

    /**
     * @param $casino
     * @param $users
     * @return array
     */
    public function prepareUsersCasinoTable($casino, $users)
    {
        $userData = collect();
        foreach ($users as $user) {
            $user->status = ($user->locked) ? "<label class='label label-sm label-danger'>" . __('Inactivo') . "</label>" : "<label class='label label-sm label-success'>" . __('Activo') . "</label>";
            $user->delete = "<a data-href='" . route('realcasino.delete-casino-user', [$casino, $user->id]) . "' class='delete-user'>
                                        <i class='fa fa-times-circle' style='color: #F3565D; font-size: 22px;'></i></a>";

            $userData->push([
                'id' => $user->id,
                "username" => $user->username,
                "status" => $user->status,
                "delete" => $user->delete
            ]);
        }
        return $userData;
    }

    /**
     * @param $casinos
     * @return \Illuminate\Support\Collection
     */
    public function prepareProfitCasinos($casinos)
    {
        $userData = collect();
        $currency = \Session::get('currency');
        foreach ($casinos as $casino) {
            $casino->casino = sprintf(
                '<a href="%s" class="btn btn-xs default">%s</a>',
                route('realcasino.casinodetails', $casino->id),
                $casino->name
            );
            $profit = $casino->played - $casino->won;
            $userData->push([
                'name' => $casino->casino,
                "description" => $casino->description,
                "played" => $casino->played,
                "won" => $casino->won,
                "profit" => $profit,
                'decimals' => intval($currency['decimals']),
                'decimalseparator' => $currency['decimalseparator'],
                'thousandseparator' => $currency['thousandseparator']
            ]);
        }
        return $userData;
    }

    /**
     * @param $transactions
     * @return array
     */
    public function prepareTotalProfitCasinos($transactions)
    {
        $currency = \Session::get('currency');
        $result = [];
        if (empty($transactions) || is_null($transactions)) {

            $result = [
                'deposit' => 0,
                'withdrawal' => 0,
                'profit' => 0,
            ];
        } else {
            foreach ($transactions as $items) {
                $profit = $items->deposit - $items->withdrawal;
                $result = [
                    'deposit' => $currency['symbol'] . " " . number_format($items->deposit, intval($currency['decimals']), $currency['decimalseparator'], $currency['thousandseparator']),
                    'withdrawal' => $currency['symbol'] . " " . number_format($items->withdrawal, intval($currency['decimals']), $currency['decimalseparator'], $currency['thousandseparator']),
                    'profit' => $currency['symbol'] . " " . number_format($profit, intval($currency['decimals']), $currency['decimalseparator'], $currency['thousandseparator']),
                ];
            }
        }
        return $result;

    }

    public function prepareUsersProfitCasino($casinos)
    {
        $userData = collect();
        $currency = \Session::get('currency');
        foreach ($casinos as $casino) {

            $casino->username = sprintf(
                '<a href="%s" class="btn btn-xs default">%s</a>',
                route('user-details', [$casino->id]),
                $casino->username
            );

            $profit = $casino->deposit - $casino->withdrawal;
            $userData->push([
                'username' => $casino->username,
                "deposit" => $casino->deposit,
                "withdrawal" => $casino->withdrawal,
                "profit" => $profit,
                'decimals' => intval($currency['decimals']),
                'decimalseparator' => $currency['decimalseparator'],
                'thousandseparator' => $currency['thousandseparator']
            ]);
        }
        return $userData;
    }

    public function graphicDashboardCasino($data)
    {
        $result = [];
        $depositTotal = 0;
        $withdrawalTotal = 0;
        $total = 0;

        foreach ($data as $item) {
            $row = new \stdClass;
            $depositTotal += $item->credit;
            $withdrawalTotal += $item->debit;
            $total += $item->credit - $item->debit;

            $row->month = $item->month;
            $row->deposit = (float)$item->credit;
            $row->withdrawal = (float)$item->debit;
            $row->totall = $item->credit - $item->debit;
            $row->deposittotal = $depositTotal;
            $row->withdrawaltotal = $withdrawalTotal;
            $row->profittotal = $total;
            $result[] = $row;
        }
        return $result;
    }
}
