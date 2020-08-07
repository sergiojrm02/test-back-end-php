<?php

namespace App\Http\Controllers;

use App\Balance;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;

class TransactionsController extends Controller
{
    private $massages = [];
    private $rules    = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->configMessagesAndRules();
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): object
    {
        $transaction = Transaction::find($id);
        if(!$transaction)
        {
            return response()->json($this->massages['show']['not_found'], JsonResponse::HTTP_NOT_FOUND);
        }
        return response()->json($transaction, JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return object
     */
    public function create(Request $request): object
    {
        $validator = \Validator::make($request->all(), $this->rules['create'], $this->massages['create']);
        if($validator->fails())
        {
            return response()->json($validator->messages(), JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(!$this->validTransaction($request->all()))
        {
            return response()->json($this->massages['create']['unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $input                               = $request->all();
        $valuePaid                           = (int) str_replace(',', '.', $input['value']);
        $saveTransaction['value']            = $valuePaid;
        $saveTransaction['transaction_date'] = Carbon::now();
        $saveTransaction['payer_id']         = $input['payer'];
        $saveTransaction['payee_id']         = $input['payee'];

        try
        {
            $transactionSave = Transaction::create($saveTransaction);
            $balanceUser     = Balance::find($input['payee']);
            if($balanceUser)
            {
                $balanceUser->value += $valuePaid;
                $balanceUser->save();
            } else
            {
                Balance::create([
                                    'user_id' => $input['payee'],
                                    'value'   => $valuePaid
                                ]);
            }

            $this->notifyTransaction($transactionSave);
            return response()->json($transactionSave, JsonResponse::HTTP_CREATED);
        } catch(\Exception $e)
        {
            Log::error('Error Create Transaction ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param $data
     * @return bool
     */
    public function validTransaction($data): bool
    {
        try
        {
            $response = Http::get(config('services.endpoint_valid_transaction'));
        } catch(\Exception $e)
        {
            Log::error('Error validTransaction ' . $e->getMessage());
            return false;
        }

        if($response->getStatusCode() == 200)
        {
            $jsonResponse = $response->getBody()->getContents();
            if(Str::slug(json_decode($jsonResponse)->message) == 'autorizado')
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $data
     * @return bool
     */
    public function notifyTransaction($data): bool
    {
        //dispatch(new ExampleJob(config('services.endpoint_notify_transaction')));
        try
        {
            $response = Http::post(config('services.endpoint_notify_transaction'));
        } catch(\Exception $e)
        {
            Log::error('Error notifyTransaction ' . $e->getMessage());
            return false;
        }

        if($response->getStatusCode() == 200)
        {
            $jsonResponse = $response->getBody()->getContents();
            if(Str::slug(json_decode($jsonResponse)->message) == 'enviado')
            {
                return true;
            }
        }
        return false;
    }

    /**
     * configMessagesAndRules
     */
    public function configMessagesAndRules()
    {
        $this->massages['create'] = [
            'payee.required' => [
                'code'    => '404',
                'message' => 'Campo de preenchimento obrigatório'
            ],
            'payee.exists'   => [
                'code'    => '424',
                'message' => 'Usuário não encontrado'
            ],
            'payer.required' => [
                'code'    => '404',
                'message' => 'Campo de preenchimento obrigatório'
            ],
            'payer.exists'   => [
                'code'    => '424',
                'message' => 'Usuário não encontrado ou não autorizado'
            ],
            'value.required' => [
                'code'    => '404',
                'message' => 'Campo de preenchimento obrigatório'
            ],
            'value.regex'    => [
                'code'    => '401',
                'message' => 'Valor não autorizado para transação'
            ],
            'value.not_in'   => [
                'code'    => '404',
                'message' => 'Valor não poder ser zero'
            ]
        ];

        $this->massages['create']['unauthorized'] = [
            'code'    => '401',
            'message' => 'Transação não autorizada'
        ];

        $this->massages['show']['not_found'] = [
            'code'    => '404',
            'message' => 'Transação não encontrada'
        ];

        $this->rules['create'] = [
            'payer' => 'required|exists:users,id,type,pf',
            'payee' => 'required|exists:users,id|different:payer',
            'value' => 'required|regex:/^([0-9]{1,20}){1}(\.[0-9]{1,2})?$/|not_in:0'
        ];
    }
}
