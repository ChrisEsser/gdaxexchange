<?php

namespace ChrisEsser\GDAXExchange;


class Exchange
{

    public $url;

    public $endpoints = [
        'accounts'          => ['method' => 'GET', 'uri' => '/accounts'],
        'account'           => ['method' => 'GET', 'uri' => '/accounts/%s'],
        'ledger'            => ['method' => 'GET', 'uri' => '/accounts/%s/ledger'],
        'holds'             => ['method' => 'GET', 'uri' => '/accounts/%s/holds'],
        'funding'           => ['method' => 'GET', 'uri' => '/funding'],
        'coinbase_accounts' => ['method' => 'GET', 'uri' => '/coinbase-accounts'],
        'coinbase_deposit'  => ['method' => 'POST', 'uri' => '/deposits/coinbase-account'],
        'place'             => ['method' => 'POST', 'uri' => '/orders'],
        'cancel'            => ['method' => 'DELETE', 'uri' => '/orders/%s'],
        'cancel_all'        => ['method' => 'DELETE', 'uri' => '/orders'],
        'orders'            => ['method' => 'GET', 'uri' => '/orders'],
        'order'             => ['method' => 'GET', 'uri' => '/orders/%s'],
        'fills'             => ['method' => 'GET', 'uri' => '/fills'],
        'products'          => ['method' => 'GET', 'uri' => '/products'],
        'book'              => ['method' => 'GET', 'uri' => '/products/%s/book'],
        'ticker'            => ['method' => 'GET', 'uri' => '/products/%s/ticker'],
        'trades'            => ['method' => 'GET', 'uri' => '/products/%s/trades'],
        'stats'             => ['method' => 'GET', 'uri' => '/products/%s/stats'],
        'rates'             => ['method' => 'GET', 'uri' => '/products/%s/candles'],
        'currencies'        => ['method' => 'GET', 'uri' => '/currencies'],
        'time'              => ['method' => 'GET', 'uri' => '/time'],
        'payments'          => ['method' => 'GET', 'uri' => '/payment-methods'],
        'payment'           => ['method' => 'POST', 'uri' => '/withdrawals/payment-method'],
        'deposit'           => ['method' => 'POST', 'uri' => '/deposits/payment-method'],
    ];

    private $key;
    private $secret;
    private $passPhrase;
    private $timestamp;

    public function __construct($test = false)
    {

        $this->url = !$test ? 'https://api.gdax.com/' : 'https://api-public.sandbox.gdax.com/';

    }

    public function auth($key, $secret, $passPhrase)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->passPhrase = $passPhrase;
    }

    protected function request($endpoint, $params = [])
    {
        extract($this->getEndpoint($endpoint, $params));

        $url = $this->url . $uri;
        $body = (!empty($params) ? json_encode($params) : '');
        $headers = [
            'User-Agent: ChrisCoinThing/v0.1',
            'Content-Type: application/json',
            'CB-ACCESS-KEY: ' . $this->key,
            'CB-ACCESS-SIGN: ' . $this->sign($method . $uri . $body),
            'CB-ACCESS-TIMESTAMP: ' . $this->timestamp,
            'CB-ACCESS-PASSPHRASE: ' . $this->passPhrase,
        ];

        $request = new Request();

        try {

            $response = $request->call($url, $method, $headers, $body);

            if ($response['statusCode'] === 200) {
                return json_decode($response['body'], true);
            }
            return $response;
        }
        catch (RequestException $e) {
            return 'Exception: ' . $e->getMessage();
        }

    }

    protected function getEndpoint($key, $params)
    {
        $endpoint = $this->endpoints[$key];
        if (empty($endpoint)) {
            throw new RequestException('Invalid endpoint ' . $key . ' specified');
        }
        if (!empty($params['id'])) {
            $endpoint['uri'] = sprintf($endpoint['uri'], $params['id']);
            unset($params['id']);
        }
        $endpoint['params'] = $params;
        return $endpoint;
    }

    protected function sign($data)
    {
        $this->timestamp = time();
        return base64_encode(hash_hmac(
            'sha256',
            $this->timestamp . $data,
            base64_decode($this->secret),
            true
        ));
    }

    public function accounts()
    {
        return $this->request('accounts');
    }

    public function account($id)
    {
        return $this->request('account', ['id' => $id]);
    }

    public function ledger($id)
    {
        return $this->request('ledger', ['id' => $id]);
    }

    public function holds($id)
    {
        return $this->request('holds', ['id' => $id]);
    }

    public function orders()
    {
        return $this->request('orders');
    }

    public function place($side, $type, $productId, $price = null, $size = null, $funds = null, $time_in_force = 'GTC', $cancel_after = 'min', $post_only = true)
    {
        $data = [
            //'client_oid' => '', // client generated UUID
            'side'          => $side,
            'type'          => $type,
            'product_id'    => $productId,
            'price'         => $price,
            'size'          => $size,
            'funds'         => $funds,
            'time_in_force' => $time_in_force,
            'cancel_after'  => $cancel_after,
            'post_only'     => $post_only,
            //'stp' => 'dc' // Or one of co, cn, cb
        ];
        return $this->request('place', $data);
    }

    public function cancel($id)
    {
        return $this->request('cancel', ['id' => $id]);
    }

    public function cancelAll($id)
    {
        return $this->request('cancel_all');
    }

    public function order($id)
    {
        return $this->request('order', ['id' => $id]);
    }

    public function fills()
    {
        return $this->request('fills');
    }

    public function products()
    {
        return $this->request('products');
    }

    public function book($product = 'BTC-USD')
    {
        //$this->validate('product', $product);
        return $this->request('book', ['id' => $product]);
    }

    public function ticker($product = 'BTC-USD')
    {
        return $this->request('ticker', ['id' => $product]);
    }

    public function trades($product = 'BTC-USD')
    {
        return $this->request('trades', ['id' => $product]);
    }

    public function rates($product = 'BTC-USD')
    {
        return $this->request('rates', ['id' => $product]);
    }

    public function stats($product = 'BTC-USD')
    {
        return $this->request('stats', ['id' => $product]);
    }

    public function currencies()
    {
        return $this->request('currencies');
    }

    public function getTime()
    {
        return $this->request('time');
    }

    public function payments()
    {
        return $this->request('payments');
    }

    public function payment($id, $amount, $currency = 'USD')
    {
        $data = [
            'amount'            => $amount,
            'currency'          => $currency,
            'payment_method_id' => $id,
        ];
        return $this->request('payment', $data);
    }

    public function deposit($id, $amount, $currency = 'USD')
    {
        $data = [
            'amount'            => $amount,
            'currency'          => $currency,
            'payment_method_id' => $id,
        ];
        return $this->request('deposit', $data);
    }

    public function coinbase_deposit($id, $amount, $currency = 'BTC')
    {
        $data = [
            'amount'              => $amount,
            'currency'            => $currency,
            'coinbase_account_id' => $id,
        ];
        return $this->request('coinbase_deposit', $data);
    }

    public function funding()
    {
        return $this->request('funding');
    }

    public function coinbase_accounts()
    {
        return $this->request('coinbase_accounts');
    }

    public function transferFunds()
    {
    }
}