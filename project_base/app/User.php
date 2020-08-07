<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'cpf',
        'email',
        'name',
        'password',
        'phone_number',
        'created_at',
        'updated_at',
        'active'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function seller()
    {
        return $this->hasOne('App\Seller', 'user_id', 'id');
    }

    public function consumer()
    {
        return $this->hasOne('App\Consumer', 'user_id', 'id');
    }
}
