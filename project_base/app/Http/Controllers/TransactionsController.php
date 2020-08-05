<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \GuzzleHttp\Client as Client;

class TransactionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create(Request $request)
    {
        $messsages = [
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
                'message' => 'Usuário não encontrado'
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
        $rules     = [
            'payee' => 'required|exists:users,id',
            'payer' => 'required|exists:users,id',
            'value' => 'required|regex:/^([0-9]{1,2}){1}(\,[0-9]{1,2})?$/|not_in:0'
        ];

        $validator = \Validator::make($request->all(), $rules, $messsages);
        if($validator->fails())
        {
            return response()->json($validator->messages(), JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(!$this->validTransaction($request->all()))
        {
            return response()->json([
                                        'code'    => '401',
                                        'message' => 'Transação não autorizada'
                                    ],
                                    JsonResponse::HTTP_UNAUTHORIZED);
        }

        $input                    = $request->all();
        $save['value']            = str_replace(',', '.', $input['value']);
        $save['transaction_date'] = Carbon::now();
        $save['payee_id']         = $input['payee'];
        $save['payer_id']         = $input['payer'];

        try
        {
            $transaction = Transaction::create($save);
            $this->notifyTransaction($transaction);

            return response()->json($transaction, JsonResponse::HTTP_CREATED);
        } catch(\Exception $e)
        {
            Log::error('Error Create Transaction ' . $e->getMessage());
            return response()->json([], JsonResponse::HTTP_NOT_FOUND);
        }
    }

    public function show($id)
    {
        $transaction = Transaction::find($id);
        if(!$transaction)
        {
            return response()->json([
                                        'code'    => '404',
                                        'message' => 'Transação não encontrada'
                                    ],
                                    JsonResponse::HTTP_NOT_FOUND);
        }
        return response()->json($transaction, JsonResponse::HTTP_OK);
    }

    public function validTransaction($data)
    {
        $client                 = new Client();
        $url                    = config('services.endpoint_valid_transaction');
        $header['Content-Type'] = 'application/json';

        try
        {
            $response = $client->post($url, [
                'headers' => $header,
                'body'    => json_encode($data)
            ]);
        } catch(\Exception $e)
        {
            Log::error('Error validTransaction ' . $e->getMessage());
            return false;
        }

        if($response->getStatusCode() == 200)
        {
            $jsonResponse = $response->getBody()->getContents();
            if(json_decode($jsonResponse)->message == 'Autorizado')
            {
                return true;
            }
        }
        return false;
    }

    public function notifyTransaction($data)
    {
        $client                 = new Client();
        $url                    = config('services.endpoint_notify_transaction');
        $header['Content-Type'] = 'application/json';

        try
        {
            $response = $client->post($url, [
                'headers' => $header,
                'body'    => json_encode($data)
            ]);
        } catch(\Exception $e)
        {
            Log::error('Error notifyTransaction ' . $e->getMessage());
            return false;
        }

        if($response->getStatusCode() == 200)
        {
            $jsonResponse = $response->getBody()->getContents();
            if(json_decode($jsonResponse)->message == 'Enviado')
            {
                return true;
            }
        }
        return false;
    }
}
