<?php

namespace App\Http\Controllers;

use App\Jobs\ExampleJob;
use Illuminate\Support\Facades\Log;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use \GuzzleHttp\Client as Client;
use phpDocumentor\Reflection\Types\Boolean;

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
    public function show($id)
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
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(Request $request)
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

        $input                    = $request->all();
        $save['value']            = str_replace(',', '.', $input['value']);
        $save['transaction_date'] = Carbon::now();
        $save['payer_id']         = $input['payer'];
        $save['payee_id']         = $input['payee'];

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

    /**
     * @param $data
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function notifyTransaction($data) : bool
    {
        //dispatch(new ExampleJob(config('services.endpoint_notify_transaction')));

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
            if(Str::slug(json_decode($jsonResponse)->message) == 'enviado')
            {
                return true;
            }
        }
        return false;
    }

    public function configMessagesAndRules()
    {
        $this->massages['create'] = [
            'payee.required' => [
                'code'    => '404',
                'message' => 'Campo de preenchimento obrigatório'
            ],
            'payee.exists'   => [
                'code'    => '424',
                'message' => 'Usuário não encontrado ou não autorizado'
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

        $this->massages['create']['unauthorized'] = [
            'code'    => '401',
            'message' => 'Transação não autorizada'
        ];

        $this->massages['show']['not_found'] = [
            'code'    => '404',
            'message' => 'Transação não encontrada'
        ];

        $this->rules['create'] = [
            'payee' => 'required|exists:consumers,user_id|different:payer',
            'payer' => 'required|exists:users,id|different:payee',
            'value' => 'required|regex:/^([0-9]{1,2}){1}(\,[0-9]{1,2})?$/|not_in:0'
        ];
    }
}
