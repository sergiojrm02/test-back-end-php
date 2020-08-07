<?php

use App\Consumer;
use App\Seller;
use App\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TransactionTest extends TestCase
{
    /**
     * Test Create Transaction.
     *
     * @return void
     */
    public function testCheckTransaction()
    {
        $userSeller = factory(User::class)->create([
                                                       'cpf'          => $this->cpf(),
                                                       'password'     => 'xxxx',
                                                       'phone_number' => '1233-3321'
                                                   ]);

        $userConsumer = factory(User::class)->create([
                                                         'cpf'          => $this->cpf(),
                                                         'password'     => 'xxxx',
                                                         'phone_number' => '1233-3321'
                                                     ]);

        $seller = factory(Seller::class)->create([
                                                     'user_id'      => $userSeller->id,
                                                     'cnpj'         => '123213123',
                                                     'fantasy_name' => 'Teste'
                                                 ]);

        $consumer = factory(Consumer::class)->create([
                                                       'user_id'  => $userConsumer->id,
                                                       'username' => 'Teste'
                                                   ]);


        $this->json('POST', '/transactions', [
            'payer' => $userSeller->id,
            'payee' => $userConsumer->id,
            'value' => '33,00'
        ])->seeJson([
                        'created' => true,
                    ]);

        //$this->json('GET', '/transactions/2');
    }

    public function cpf($compontos = false)
    {
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $n5 = rand(0, 9);
        $n6 = rand(0, 9);
        $n7 = rand(0, 9);
        $n8 = rand(0, 9);
        $n9 = rand(0, 9);
        $d1 = $n9 * 2 + $n8 * 3 + $n7 * 4 + $n6 * 5 + $n5 * 6 + $n4 * 7 + $n3 * 8 + $n2 * 9 + $n1 * 10;
        $d1 = 11 - (fmod($d1, 11));
        if($d1 >= 10)
        {
            $d1 = 0;
        }
        $d2 = $d1 * 2 + $n9 * 3 + $n8 * 4 + $n7 * 5 + $n6 * 6 + $n5 * 7 + $n4 * 8 + $n3 * 9 + $n2 * 10 + $n1 * 11;
        $d2 = 11 - (fmod($d2, 11));
        if($d2 >= 10)
        {
            $d2 = 0;
        }
        $retorno = '';
        if($compontos == 1)
        {
            $retorno = '' . $n1 . $n2 . $n3 . "." . $n4 . $n5 . $n6 . "." . $n7 . $n8 . $n9 . "-" . $d1 . $d2;
        } else
        {
            $retorno = '' . $n1 . $n2 . $n3 . $n4 . $n5 . $n6 . $n7 . $n8 . $n9 . $d1 . $d2;
        }
        return $retorno;
    }
}
