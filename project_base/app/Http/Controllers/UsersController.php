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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function filters(Request $request) : object
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
                              'users.identifier',
                              'users;type',
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

    public function show($id) : object
    {
        return response()->json(User::with('seller', 'consumer')->get()->find($id), JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return object
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request) : object
    {
        $this->validate($request, [
            'email'      => 'required|unique:users',
            'identifier' => 'required|unique:users|min:11|max:18',
            'name'       => 'required',
            'password'   => 'required',
            'type'       => 'required|in:pj,pf'
        ]);

        $input               = $request->all();
        $input['identifier'] = $this->removeStringByInteger($input['identifier']);
        $user                = User::create($input);

        return response()->json($user, JsonResponse::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createConsumers(Request $request) : object
    {
        $this->validate($request, [
            'user_id'  => 'required|exists:users,id',
            'username' => 'required|unique:consumers|unique:sellers'
        ]);

        $consumer = Consumer::create($request->all());
        return response()->json($consumer, JsonResponse::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return object
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createSellers(Request $request) : object
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

    /**
     * @param $value
     * @return string
     */
    public function removeStringByInteger($value) : string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }
}
