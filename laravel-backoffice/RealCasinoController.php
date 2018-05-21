<?php

namespace JEL\Http\Controllers;


use Dotworkers\Configurations\Configurations;
use JEL\Core\Enums\Utils;
use Illuminate\Http\Request;
use JEL\Core\Enums\Status;
use JEL\Core\Enums\StatusReponse;
use JEL\Core\Enums\TransactionType;
use JEL\NodeRedis\NodeRedis;
use JEL\Paginator\PaginatorDataTable;
use JEL\RealCasino\Collections\RealCasinoCollection;
use JEL\RealCasino\Repositories\RealCasinoRepo;
use Juegaenlinea\Security\Security;
use Yajra\Datatables\Datatables;
use Juegaenlinea\Walletwrapper\Wallet;
use Webpatser\Uuid\Uuid;
use Juegaenlinea\Walletwrapper\Enums\Actions;
use Juegaenlinea\Walletwrapper\Enums\Providers;
use JEL\Agents\Repositories\CountriesRepo;
use JEL\Users\Managers\UserManager;
use Juegaenlinea\Store\Store;
use DateTimeZone;
use JEL\Users\Repositories\UsersRepo;
use JEL\Users\Repositories\ProfileRepo;
use JEL\Users\Contracts\Users;
use JEL\Core\Repositories\CountryRepo;
use JEL\Core\Repositories\CreditTransactionsRepo;
use JEL\Core\Repositories\DebitTransactionsRepo;
use JEL\Users\Entities\UserTemp;
use JEL\Users\Managers\PasswordManager;
use JEL\Users\Collections\UsersCollection;

/**
 * Class RealCasinoController
 *
 * @package JEL\Http\Controllers
 * @author Carol Mirabal
 */
class RealCasinoController extends Controller
{
    private $realcasinoRepo;

    private $realcasinoCollection;

    private $usersRepo;

    private $countriesRepo;

    private $profileRepo;

    /**
     * Class construct
     *
     * RealCasinoController constructor.
     * @param RealCasinoRepo $realcasinoRepo
     * @param RealCasinoCollection $realcasinoCollection
     * @param UsersRepo $usersRepo
     * @param CountriesRepo $countriesRepo
     * @param ProfileRepo $profileRepo
     */
    public function __construct(RealCasinoRepo $realcasinoRepo, RealCasinoCollection $realcasinoCollection, UsersRepo $usersRepo, CountriesRepo $countriesRepo, ProfileRepo $profileRepo)
    {
        $this->realcasinoRepo = $realcasinoRepo;
        $this->realcasinoCollection = $realcasinoCollection;
        $this->usersRepo = $usersRepo;
        $this->countriesRepo = $countriesRepo;
        $this->profileRepo = $profileRepo;


    }

    /**
     * Transaction cancelled process
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelledTransaction(Request $request)
    {
        try {
            $transaction = $request->transaction;
            $user = $request->user;
            $operator = \Auth::user()->id;
            $status = Status::$cancelled;
            $transactiontype = $request->transactiontype;
            $data = $this->realcasinoRepo->findTransaction($transaction, $user, $transactiontype);
            if (count($data) > 0) {
                if ($data->status == Status::$pending) {
                    $update = $this->realcasinoRepo->transactionUpdate($transaction, $user, $amount = null, $status, $operator, $transaction = null);
                    return bodyResponseRequest(StatusReponse::$success, $update);
                } else {
                    return bodyResponseRequest(StatusReponse::$customfailed, [], 'Esta Solicitud ya fue procesada');
                }
            }
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealCasinoController.cancelledTransaction.catch');
        }
    }

    /**
     * Transactions approved
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedTransaction(Request $request)
    {
        try {
            $transaction = $request->transaction;
            $walletstatus = null;
            $user = $request->user;
            $operator = \Auth::user()->id;
            $amount = $request->amount;
            $transactiontype = $request->transactiontype;
            $data = $this->realcasinoRepo->findTransaction($transaction, $user, $transactiontype);
            $userkey = $this->realcasinoRepo->getKey(env('ID'), $user);
            if (count($data) > 0) {
                if ($data->status == Status::$pending) {
                    switch ($transactiontype) {
                        case TransactionType::$credit: {

                            $findcasino = $this->realcasinoRepo->findCasino(env('ID'), $user);

                            $wallet_casino = json_decode(Wallet::debitTransactions(
                                $findcasino->casino,
                                $amount,
                                Providers::$real_casino,
                                Actions::$manual,
                                "Debito por Deposito de Usuario"
                            ));
                            $status = $wallet_casino->status;

                            if ($status == 'OK') {
                                $wallet_transaction = json_decode(Wallet::creditTransactions(
                                    $user,
                                    $amount,
                                    Providers::$real_casino,
                                    Actions::$manual,
                                    "Deposito Aprobado"
                                ));

                                $walletstatus = $wallet_transaction->status;
                            } else {
                                return bodyResponseRequest(StatusReponse::$customfailed, [], 'Este monto sobrepasa el limite actual del casino');
                            }
                            break;
                        }
                        case TransactionType::$debit: {
                            if ($amount <= json_decode(\Wallet::getWallet($user))->data->wallet->balance) {
                                $wallet_transaction = json_decode(Wallet::debitTransactions(
                                    $user,
                                    $amount,
                                    Providers::$real_casino,
                                    Actions::$manual,
                                    "Retiro Aprobado"
                                ));
                                $walletstatus = $wallet_transaction->status;
                            } else {
                                return bodyResponseRequest(StatusReponse::$customfailed, [], 'Este monto es mayor que el balance');
                            }
                            break;
                        }
                    }
                    if ($walletstatus == "OK") {
                        $currency = \Session::get('currency');
                        $update = $this->realcasinoRepo->transactionUpdate($transaction, $user, $amount, Status::$approved, $operator, $wallet_transaction->data->transaction->id);
                        $data = [
                            'user' => $user,
                            'balance' => number_format($wallet_transaction->data->wallet->balance, 2, ',', '.'),
                            'username' => $userkey->username,
                            'idwl' => env('ID')
                        ];

                        NodeRedis::RedisPublish($wl = 'whitelabel', $type = 'updatebalance', $module = 'realcasinoclient', \Session::get('currency')['iso'], env('WHITELABEL'), $data);

                        return bodyResponseRequest(StatusReponse::$success, $update);
                    }
                } else {
                    return bodyResponseRequest(StatusReponse::$customfailed, [], 'Esta Solicitud ya fue procesada');
                }
            }
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealCasinoController.approvedTransaction.catch');
        }
    }

    /**
     * Get all  transactions for datatable
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTransactions(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $transactions = $this->realcasinoRepo->getAllTransactions(\Session::get('currency')['iso'], \Auth::user()->whitelabel, \Session::get('timezone'), $startDate, $endDate, \Auth::user()->id);
            $data = $this->realcasinoCollection->allTransactions($transactions);
            $result = Datatables::of($data)->make();
            return $result;
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getAllTransactions.catch');
        }
    }

    /**
     * Get totals transactions
     *
     * @param Request $request
     * @return mixed
     */
    public function getTotalsTransactions(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $transactions = $this->realcasinoRepo->totalTransactions(\Session::get('timezone'), $startDate, $endDate, \Auth::user()->whitelabel, \Session::get('currency')['iso'], \Auth::user()->id);
            $data = $this->realcasinoCollection->totalsTransactions($transactions);
            return response()->json($data);
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getTotalsTransactions.catch');
        }
    }

    /**
     * Graphic dashboard casino
     *
     * @param Request $request
     * @return mixed
     */
    public function graphicDashboardCasino(Request $request)
    {
        try {
            $startDate = $request->dateAt;
            $endDate = $request->dateFrom;
            $transactions = $this->realcasinoRepo->graphicDashboardCasino(\Session::get('timezone'), \Auth::user()->id, \Auth::user()->whitelabel, $startDate, $endDate, \Session::get('currency')['iso']);
            $data = $this->realcasinoCollection->graphicDashboardCasino($transactions);
            $response = ['dataProvider' => $data];
            return response()->json($response);
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.graphicDashboardCasino.catch');
        }
    }

    /**
     * Get report transactions withdrawal
     *
     * @param Request $request
     * @return mixed
     */
    public function getWithdrawalTransactions(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            if (empty($startDate) && empty($endDate)) {
                return PaginatorDataTable::firstLoad();
            } else {
                $transactions = $this->realcasinoRepo->getWithdrawalTransactions(\Session::get('currency')['iso'], \Auth::user()->whitelabel, \Session::get('timezone'), $startDate, $endDate, \Auth::user()->id);
                $data = $this->realcasinoCollection->reportTransactions($transactions);
                $result = Datatables::of($data)->make();
                return $result;
            }
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getWithdrawalTransactions.catch');
        }
    }

    /**
     * Get report transactions deposit
     *
     * @param Request $request
     * @return mixed
     */
    public function getDepositTransactions(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            if (empty($startDate) && empty($endDate)) {
                return PaginatorDataTable::firstLoad();
            } else {
                $transactions = $this->realcasinoRepo->getDepositTransactions(\Session::get('currency')['iso'], \Auth::user()->whitelabel, \Session::get('timezone'), $startDate, $endDate, \Auth::user()->id);
                $data = $this->realcasinoCollection->reportTransactions($transactions);
                $result = Datatables::of($data)->make();
                return $result;
            }
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getDepositTransactions.catch');
        }
    }

    /**
     * blocked screen client
     *
     * @param Request $request
     * @return mixed
     */
    public function blockedUser(Request $request)
    {
        try {
            $userkey = $this->realcasinoRepo->getKey(env('ID'), $request->user);
            $data = [
                'user' => $request->user,
                'message' => __('Equipo Bloqueado'),
                'username' => $userkey->username,
                'idwl' => env('ID')
            ];
            switch ($request->state) {
                case 'false':
                    NodeRedis::RedisPublish($wl = 'whitelabel', $type = 'block', $module = 'realcasinoclient', \Session::get('currency')['iso'], env('WHITELABEL'), $data);
                    break;
                case 'true':
                    NodeRedis::RedisPublish($wl = 'whitelabel', $type = 'unblock', $module = 'realcasinoclient', \Session::get('currency')['iso'], env('WHITELABEL'), $data);
                    break;
            }
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.blockedUser.catch');
        }
    }

    /**
     * Logout remote client casino
     *
     * @param Request $request
     * @return mixed
     */
    public function logoutUser(Request $request)
    {
        try {
            $userkey = $this->realcasinoRepo->getKey(env('ID'), $request->user);
            $data = [
                'user' => $request->user,
                'username' => $userkey->username,
                'idwl' => env('ID')
            ];
            NodeRedis::RedisPublish($wl = 'whitelabel', $type = 'endsession', $module = 'realcasinoclient', \Session::get('currency')['iso'], env('WHITELABEL'), $data);
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.logoutUser.catch');
        }
    }

    /**
     * Login remote client casino
     *
     * @param Request $request
     * @return mixed
     */
    public function loginUser(Request $request)
    {
        try {
            $userkey = $this->realcasinoRepo->getKey(env('ID'), $request->user);
            $data = [
                'user' => $request->user,
                'username' => $userkey->username,
                'idwl' => env('ID')
            ];
            NodeRedis::RedisPublish($wl = 'whitelabel', $type = 'loginuser', $module = 'realcasinoclient', \Session::get('currency')['iso'], env('WHITELABEL'), $data);
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.loginUser.catch');
        }
    }

    /**
     * Panel administration users
     *
     * @return mixed
     */
    public function panelAdministration()
    {
        try {
            $casino = $this->realcasinoRepo->findCasinoByUserCasino(env('ID'), \Auth::user()->id);
            $transactions = $this->realcasinoRepo->getUsers(\Auth::user()->whitelabel, $casino->id);
            $data = $this->realcasinoCollection->panelAdministration($transactions);
            $result = Datatables::of($data)->make();
            return $result;
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.panelAdministration.catch');
        }
    }

    /**
     * Get casinos by owner
     *
     * @return mixed
     */
    public function getCasinos()
    {
        try {
            $casinos = $this->realcasinoRepo->getCasinos(\Auth::user()->id);
            $data = $this->realcasinoCollection->usersPlaybox($casinos);
            $result = Datatables::of($data)->make();
            return $result;
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getCasinos.catch');
        }
    }

    /**
     * Details casino and users
     *
     * @param $group
     * @return Factory|\Illuminate\View\View
     */
    public function getDetailCasinoById($group)
    {
        try {
            $detailsCasinoUsers = $this->realcasinoRepo->getUsersCasino($group);
            $details = $this->realcasinoCollection->prepareUsersCasinoTable($group, $detailsCasinoUsers);
            $result = Datatables::of($details)->make();
            return $result;
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealCasinoController.getDetailCasinoById.catch');
        }
    }

    /**
     * Add users to the casino
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addUserToCasino()
    {
        try {
            $users = \Request::input('users');
            $casino = \Request::input('casino');

            if (!is_null($users)) {
                foreach ($users as $user) {
                    $details = $this->realcasinoRepo->getUserById($user);
                    if (empty($details)) {
                        $this->realcasinoRepo->addUsersCasino($casino, $user);
                    } else {
                        return bodyResponseRequest(StatusReponse::$customfailed, [], _('Este Usuario ya pertenece a un casino'));
                    }
                }
            }
            return bodyResponseRequest(StatusReponse::$success, []);
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealCasinoController.addUserToCasino.catch');
        }
    }

    /**
     * Delete a user of the casino
     *
     * @param $group
     * @param $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUsersCasino($group, $user)
    {
        try {
            $details = $this->realcasinoRepo->deleteUsersCasino($group, $user);
            return bodyResponseRequest(StatusReponse::$success, []);
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealCasinoController.deleteUsersCasino.catch');
        }
    }

    /**
     * Create casinos by owner
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createCasino(Request $request)
    {
        try {
            $casino = $request->casino_name;
            $description = $request->casino_description;
            $username = strtolower($request->username);
            $password = $request->password;
            $email = strtolower($request->email);
            $key = (string)\Uuid::generate(4);
            $whiteLabel = Configurations::findWhiteLabelByName(env('WHITELABEL'))->id;
            $currency = \Session::get('currency')['iso'];
            $timezone = $request->timezone;
            $country = $request->country;
            $valueofNegotiation = $request->descMto;
            $typeofnegotiation = $request->typefinancial;
            $limit = $request->limit;

            $data = ['username' => $username, 'email' => $email, 'password' => $password, 'key' => $key, 'whitelabel' => $whiteLabel, 'currency' => $currency];

            $system_users = json_decode(Wallet::getSystemUsers())->data->users;

            if (!$this->usersRepo->userExists($whiteLabel, $username) && !$this->usersRepo->emailExists($whiteLabel, $email) && !Utils::in_array_key($system_users, 'username', $username)) {

                $user = $this->usersRepo->register($username, $password, $email, $key, $whiteLabel, $currency);

                // Manager instance
                $manager = new UserManager($user, $data);

                // Checks that the country is not empty
                if (!isset($country) || empty($country)) {
                    return bodyResponseRequest(StatusReponse::$customfailed, [], 'Debe seleccionar un país');
                }

                // Checks that the timezone is not empty
                if (!isset($timezone) || empty($timezone)) {
                    return bodyResponseRequest(StatusReponse::$customfailed, [], 'El campo Zona Horaria es obligatorio.');
                }

                // If validation pass
                if ($manager->save()) {

                    $profile = $this->profileRepo->create($user->id, $country, null, null, $timezone, null, null);

                    $this->realcasinoRepo->createCasino($user->id, $description, $casino, $whiteLabel, \Auth::user()->id, $valueofNegotiation, $typeofnegotiation, $limit);

                    Security::addRole($user->id, 23);

                    // Creating a money wallet

                    Wallet::create($user->id, $user->username, $key, $user->whitelabel, true, $currency);

                    // Creating a points wallet
                    Wallet::create($user->id, $user->username, $key, $user->whitelabel, false, 'PTS');

                    // Creating initial historic points balance

                    Store::createBalance($user->id, $limit);

                    return bodyResponseRequest(StatusReponse::$customsuccess, [], 'Usuario Creado');
                } else {
                    \Log::error('RealCasinoController.createCasino', [$manager->getError()]);

                    return bodyResponseRequest(StatusReponse::$failed, $manager->getError());
                }
            } else {
                return bodyResponseRequest(StatusReponse::$customfailed, [], 'Usuario duplicado en el sistema');
            }

            return bodyResponseRequest(StatusReponse::$success, []);
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealCasinoController.createCasino.catch');
        }
    }

    /**
     * Create users by casino
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createUsersCasino(Request $request)
    {
        try {
            $username = strtolower($request->username);
            $password = $request->password;
            $email = strtolower($request->email);
            $key = (string)\Uuid::generate(4);
            $whiteLabel = Configurations::findWhiteLabelByName(env('WHITELABEL'))->id;
            $currency = \Session::get('currency')['iso'];
            $timezone = $request->timezone;
            $country = $request->country;

            $data = ['username' => $username, 'email' => $email, 'password' => $password, 'key' => $key, 'whitelabel' => $whiteLabel, 'currency' => $currency];

            $system_users = json_decode(Wallet::getSystemUsers())->data->users;

            if (!$this->usersRepo->userExists($whiteLabel, $username) && !$this->usersRepo->emailExists($whiteLabel, $email) && !Utils::in_array_key($system_users, 'username', $username)) {

                $user = $this->usersRepo->register($username, $password, $email, $key, $whiteLabel, $currency);

                // Manager instance
                $manager = new UserManager($user, $data);

                // Checks that the country is not empty
                if (!isset($country) || empty($country)) {
                    return bodyResponseRequest(StatusReponse::$customfailed, [], 'Debe seleccionar un país');
                }

                // Checks that the timezone is not empty
                if (!isset($timezone) || empty($timezone)) {
                    return bodyResponseRequest(StatusReponse::$customfailed, [], 'El campo Zona Horaria es obligatorio.');
                }

                // If validation pass
                if ($manager->save()) {

                    $profile = $this->profileRepo->create($user->id, $country, null, null, $timezone, null, null);

                    $casino = $this->realcasinoRepo->findCasinoById($whiteLabel, \Auth::user()->id);

                    $this->realcasinoRepo->addUsersCasino($casino->id, $user->id);

                    Security::addRole($user->id);

                    // Creating a money wallet

                    Wallet::create($user->id, $user->username, $key, $user->whitelabel, true, $currency);

                    // Creating a points wallet
                    Wallet::create($user->id, $user->username, $key, $user->whitelabel, false, 'PTS');

                    // Creating initial historic points balance

                    Store::createBalance($user->id);

                    return bodyResponseRequest(StatusReponse::$customsuccess, [], 'Usuario Creado');
                } else {
                    \Log::error('RealCasinoController.createCasino', [$manager->getError()]);

                    return bodyResponseRequest(StatusReponse::$failed, $manager->getError());
                }
            } else {
                return bodyResponseRequest(StatusReponse::$customfailed, [], 'Usuario duplicado en el sistema');
            }

            return bodyResponseRequest(StatusReponse::$success, []);
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealCasinoController.createUsersCasino.catch');
        }
    }

    /**
     * Get Profit Casinos
     *
     * @return mixed
     */
    public function getProfitCasinos(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            if (empty($startDate) && empty($endDate)) {
                return PaginatorDataTable::firstLoad();
            } else {
                $casinos = $this->realcasinoRepo->getProfitCasinos(\Auth::user()->id, \Session::get('timezone'), $startDate, $endDate);
                $data = $this->realcasinoCollection->prepareProfitCasinos($casinos);
                return Datatables::of($data)->make();
            }
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getProfitCasinos.catch');
        }
    }

    /**
     * Get total profit casinos
     *
     * @param Request $request
     * @return mixed
     */
    public function getTotalProfitCasinos(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $casinos = $this->realcasinoRepo->getTotalProfitCasinos(\Auth::user()->id, \Session::get('timezone'), $startDate, $endDate);
            $data = $this->realcasinoCollection->prepareTotalProfitCasinos($casinos);
            return response()->json($data);
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getTotalProfitCasino.catch');
        }
    }

    /**
     * Get profit all users
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfitUsersCasino(Request $request)
    {
        try {

            $startDate = $request->startDate;
            $endDate = $request->endDate;
            if (empty($startDate) && empty($endDate)) {
                return PaginatorDataTable::firstLoad();
            } else {
                $casinos = $this->realcasinoRepo->getProfitUsersCasino(\Auth::user()->id, \Session::get('timezone'), $startDate, $endDate);
                $data = $this->realcasinoCollection->prepareUsersProfitCasino($casinos);
                return Datatables::of($data)->make();
            }
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getProfitUsersCasino.catch');
        }
    }

    /**
     * Get total profit casinos
     *
     * @param Request $request
     * @return mixed
     */
    public function getTotalProfitUsersCasino(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $casinos = $this->realcasinoRepo->getTotalProfitUsersCasino(\Auth::user()->id, \Session::get('timezone'), $startDate, $endDate);
            $data = $this->realcasinoCollection->prepareTotalProfitCasinos($casinos);
            return response()->json($data);
        } catch (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getTotalProfitCasino.catch');
        }
    }


    /**
     * Get profit all users by casino
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfitUsersByCasino(Request $request)
    {
        try {
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $casino = $request->casino;
            if ($casino == "") {
                return bodyResponseRequest(StatusReponse::$failedofvalidation, __('El Casino no debe estar vacío'));
            } else {
                if (empty($startDate) && empty($endDate)) {
                    return PaginatorDataTable::firstLoad();
                } else {
                    $casinos = $this->realcasinoRepo->getProfitUsersCasino($casino, \Session::get('timezone'), $startDate, $endDate);
                    $data = $this->realcasinoCollection->prepareUsersProfitCasino($casinos);
                    $result = Datatables::of($data)->make();
                    return $result;
                }
            }
        } catch
        (\Exception $ex) {
            return bodyResponseRequest(StatusReponse::$error, $ex, [], 'RealCasinoController.getProfitUsersByCasino.catch');
        }
    }

    /**
     * Edit casino from details
     *
     * @return mixed
     */
    public function editCasino()
    {

        $field = \Request::input('name');
        $value = \Request::input('value');
        $casinoid = \Request::input('pk');

        $result = $this->realcasinoRepo->updateCasino($casinoid, $field, $value);

        return $result;
    }

    /**
     * Edit casino limit
     *
     * @param Request $request
     * @param $casino
     * @return mixed
     */
    public function editLimit(Request $request, $casino)
    {
        $limit = $request->limit;
        $detailscasino = $this->realcasinoRepo->findCasinoById(env('ID'), $casino);
        $balance = json_decode(\Wallet::getWallet($detailscasino->casino))->data->wallet->balance;
        if ($balance <> 0) {
            $wallet_debit = json_decode(Wallet::debitTransactions(
                $detailscasino->casino,
                $balance,
                Providers::$real_casino,
                Actions::$manual,
                "Edicion de limite de casino"
            ));
        }
        $wallet_credit = json_decode(Wallet::creditTransactions(
            $detailscasino->casino,
            $limit,
            Providers::$real_casino,
            Actions::$manual,
            "Edicion de limite de casino"
        ));

        $result = $this->realcasinoRepo->updateLimit($casino, $limit);

        return $result;
    }

    /**
     * Edit casino limit
     *
     * @param Request $request
     * @param $casino
     * @return mixed
     */
    public function editDescription(Request $request, $casino)
    {
        $description = $request->description;
        $result = $this->realcasinoRepo->updateDescription($casino, $description);

        return $result;
    }

    /**
     * Search casino select
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCasinosSelect(Request $request)
    {
        try {
            $casino = $request->casino['term'];
            $data = $this->realcasinoRepo->findCasinosSelect($casino, \Auth::user()->id);
            return response()->json($data);
        } catch (\Exception $e) {
            return bodyResponseRequest(StatusReponse::$error, $e, [], 'RealgamesController.searchCasinosSelect.catch');
        }
    }

    /**
     * Return view all transaction process
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewAllTransactions()
    {
        return view('realcasino.alltransactions');
    }

    /**
     * Return view profit casinos
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewProfitCasinos()
    {
        return view('realcasino.reports.profitcasinos');
    }

    /**
     * Return view profit by casino
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewProfitByCasino()
    {
        return view('realcasino.reports.profitbycasino');
    }

    /**
     * Return view Users profit of casino
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewProfitUsersCasino()
    {
        return view('realcasino.reports.profituserscasino');
    }


    /**
     * Return view report deposit transaction
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewDepositTransactions()
    {
        return view('realcasino.reports.deposittransactions');
    }

    /**
     * Return view report withdrawal transaction
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewWithdrawalTransactions()
    {
        return view('realcasino.reports.withdrawaltransactions');
    }

    /**
     * Return view panel administration casino
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewPanel()
    {
        return view('realcasino.panel');
    }


    /**
     * Return view casinos
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewCasinos()
    {
        $data['countries'] = $this->countriesRepo->all();
        $data['timezones'] = DateTimeZone::listIdentifiers();
        return view('realcasino.casinos', $data);
    }

    /**
     * Return view Create users
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewCreateUsers()
    {
        $data['countries'] = $this->countriesRepo->all();
        $data['timezones'] = DateTimeZone::listIdentifiers();
        return view('realcasino.createusercasino', $data);
    }

    /**
     * Return view casinos details
     *
     * @param $casino
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewCasinoDetailsUsers($casino)
    {
        $detailscasino = $this->realcasinoRepo->findCasinoById(env('ID'), $casino);
        $data['casino'] = $detailscasino;
        $rest = number_format(json_decode(\Wallet::getWallet($detailscasino->casino))->data->wallet->balance, 2, ',', '.');
        $data['limit'] = $rest;
        return view('realcasino.casinodetails', $data);
    }


}
