<?php

namespace JEL\Http\Controllers;


use Illuminate\Http\Request;
use Dotworkers\Configurations\Configurations;
use JEL\Core\Enums\ChannelRedis;
use JEL\Core\Enums\Status;
use JEL\Core\Enums\TransactionType;
use JEL\NodeRedis\NodeRedis;
use JEL\RealCasino\Repositories\RealCasinoRepo;
use Webpatser\Uuid\Uuid;


/**
 * Class RealCasinoController
 *
 * Real Casino
 *
 * @package JEL\Http\Controllers
 * @author  Carol Mirabal
 */
class RealCasinoController extends Controller
{
    private $realcasinoRepo;

    public function __construct(RealCasinoRepo $realcasinoRepo)
    {
        $this->realcasinoRepo = $realcasinoRepo;
    }

    /**
     * Request Deposit User
     *
     * @param Request $request
     * @return array
     */
    public function requestDeposit(Request $request)
    {
        try {
            $whitelabelid = env('ID');
            $user = \Auth::user()->id;
            $transactiontype = TransactionType::$credit;
            $status = Status::$pending;
            $id_transaction = $uuid = Uuid::generate(4)->string;
            $currency = \Auth::user()->currency;
            $whitelabel = env('WHITELABEL');

            if (!empty($request->match)) {


                $balance = json_decode(\Wallet::getWallet(\Auth::user()->id))->data->wallet->balance + \SkyGammingUtils::getBalance();
                $findcasino = $this->realcasinoRepo->findCasino($user);
                $casino = $findcasino->casino;

                $findtransaction = $this->realcasinoRepo->findTransactionPending($user, $status);


                if (count($findtransaction) == 0) {

                    $data = [
                        'transaction' => $id_transaction,
                        'username' => \Auth::user()->username,
                        'transactiontype' => TransactionType::$credit,
                        'user' => $user,
                        'casino' => $casino
                    ];

                    $transaction = $this->realcasinoRepo->requestTransaction($id_transaction, $status, $transactiontype, $user, $whitelabelid, $currency, $balance);

                    if ($transaction) {

                        NodeRedis::RedisPublish(ChannelRedis::$playbox, $type = 'request', $module = 'realcasino', $currency, $whitelabel, $data);

                        $response = [
                            'status' => 'SUCCESS',
                            'title' => __('Exitoso!'),
                            'message' => __('Se ha enviado su solicitud de depósito pronto será atendido'),
                        ];
                    } else {
                        $response = [
                            'status' => 'ERROR',
                            'title' => __('Error'),
                            'message' => __('Hubo un error al enviar su solicitud!'),
                        ];
                    }
                } else {
                    $response = [
                        'status' => 'ERROR',
                        'title' => __('Error!'),
                        'message' => __('Tiene Otra solicitud de deposito pendiente, Pronto será atendido'),
                    ];
                }
            } else {
                $response = [
                    'status' => 'ERROR',
                    'title' => __('Error!'),
                    'message' => __('No puede realizar esta acción, este dispositivo aun no está conectado'),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('RealCasinoController.requestDeposit.catch', ['exception' => $e]);
            $response = [
                'status' => 'ERROR',
                'title' => __('Error'),
                'message' => __('Hubo un error al enviar su solicitud'),
            ];
        }
        return response()->json($response);
    }

    /**
     * Request Withdrawal User
     *
     * @param Request $request
     * @return array
     */
    public function requestWithdrawal(Request $request)
    {
        try {
            $whitelabelid = env('ID');
            $user = \Auth::user()->id;
            $transactiontype = TransactionType::$debit;
            $status = Status::$pending;
            $id_transaction = $uuid = Uuid::generate(4)->string;
            $currency = \Auth::user()->currency;
            $whitelabel = env('WHITELABEL');

            if (!empty($request->match)) {

                $findcasino = $this->realcasinoRepo->findCasino($user);
                $casino = $findcasino->casino;
                $findtransaction = $this->realcasinoRepo->findTransactionPending($user, $status);
                $balance = json_decode(\Wallet::getWallet(\Auth::user()->id))->data->wallet->balance + \SkyGammingUtils::getBalance();
                if (count($findtransaction) == 0) {
                    $data = [
                        'transaction' => $id_transaction,
                        'username' => \Auth::user()->username,
                        'transactiontype' => TransactionType::$debit,
                        'user' => $user,
                        'casino' => $casino,
                        'balance' => $balance
                    ];

                    $transaction = $this->realcasinoRepo->requestTransaction($id_transaction, $status, $transactiontype, $user, $whitelabelid, $currency, $balance);

                    if ($transaction) {
                        $status_response = \SkyGammingUtils::withdrawal();
                        NodeRedis::RedisPublish(ChannelRedis::$playbox, $type = 'request', $module = 'realcasino', $currency, $whitelabel, $data);
                        $response = [
                            'status' => 'SUCCESS',
                            'title' => __('Exitoso!'),
                            'message' => __('Se ha enviado su solicitud de retiro pronto será atendido'),
                        ];
                    } else {
                        $response = [
                            'status' => 'ERROR',
                            'title' => __('Error'),
                            'message' => __('Hubo un error al enviar su solicitud!'),
                        ];
                    }
                } else {
                    $response = [
                        'status' => 'ERROR',
                        'title' => __('Error!'),
                        'message' => __('Tiene Otra solicitud de retiro pendiente, Pronto será atendido'),
                    ];
                }
            } else {
                $response = [
                    'status' => 'ERROR',
                    'title' => __('Error!'),
                    'message' => __('No puede realizar esta acción, este dispositivo aun no está conectado'),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('RealCasinoController.requestWithdrawal.catch', ['exception' => $e]);
            $response = [
                'status' => 'ERROR',
                'title' => __('Error'),
                'message' => __('Hubo un error al enviar su solicitud'),
            ];
        }
        return response()->json($response);
    }

    /**
     * Send messages casino playbox
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request)
    {
        try {

            $user = \Auth::user()->id;
            $findcasino = $this->realcasinoRepo->findCasino($user);
            $casino = $findcasino->casino;
            $currency = \Auth::user()->currency;
            $whitelabel = env('WHITELABEL');
            $data = [
                'username' => \Auth::user()->username,
                'user' => $user,
                'casino' => $casino,
                'message' => $request->message
            ];

            NodeRedis::RedisPublish(ChannelRedis::$playbox, $type = 'chat', $module = 'realcasino', $currency, $whitelabel, $data);

            $response = [
                'status' => 'SUCCESS',
                'title' => __('Exitoso!'),
                'message' => __('Se ha enviado su solicitud de retiro pronto será atendido'),
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('RealCasinoController.sendMessage.catch', ['exception' => $e]);
        }
    }
}
