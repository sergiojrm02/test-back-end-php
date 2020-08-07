<?php

namespace App\Http\Controllers;

use App\Consumer;
use App\Seller;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function filters(Request $request)
    {
        $user = User::where('users.name', 'like', $request->query('q') . '%')
                    ->orWhere('sellers.username', 'like', $request->query('q') . '%')
                    ->orWhere('consumers.username', 'like', $request->query('q') . '%')
                    ->leftJoin('sellers', 'users.id', '=', 'sellers.user_id')
                    ->leftJoin('consumers', 'users.id', '=', 'consumers.user_id')
                    ->with('seller', 'consumer')
                    ->orderBy('name', 'asc')
                    ->get([
                              'users.id',
                              'users.cpf',
                              'users.email',
                              'users.name',
                              'users.phone_number'
                          ])->toArray();

        if(empty($user))
        {
            return response()->json($user, JsonResponse::HTTP_NO_CONTENT);
        }
        return response()->json($user, JsonResponse::HTTP_OK);
    }

    public function show($id)
    {
        return response()->json(User::with('seller', 'consumer')->get()->find($id), JsonResponse::HTTP_OK);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|unique:users',
            'cpf'   => 'required|unique:users|min:11|max:14'
        ]);

        $input        = $request->all();
        $input['cpf'] = $this->removeStringByInteger($input['cpf']);
        $user         = User::create($input);

        return response()->json($user, JsonResponse::HTTP_CREATED);
    }

    public function createConsumers(Request $request)
    {
        $this->validate($request, [
            'user_id'  => 'required|exists:users,id',
            'username' => 'required|unique:consumers|unique:sellers'
        ]);

        $consumer = Consumer::create($request->all());
        return response()->json($consumer, JsonResponse::HTTP_CREATED);
    }

    public function createSellers(Request $request)
    {
        $this->validate($request, [
            'user_id'  => 'required|exists:users,id',
            'username' => 'required|unique:sellers|unique:consumers',
            'cnpj'     => 'required|min:14|max:18'
        ]);

        $input         = $request->all();
        $input['cnpj'] = $this->removeStringByInteger($input['cnpj']);

        $seller = Seller::create($input);
        return response()->json($seller, JsonResponse::HTTP_CREATED);
    }

    public function removeStringByInteger($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}
