<?php

namespace Damon\Support;

use Zttp\Zttp;
use Damon\YouzanPay;

abstract class Gateway
{
    const API_GATEWAY = 'https://open.youzan.com/api/oauthentry/%s/%s/%s?access_token=%s';

    /**
     * YouzanPay instance
     *
     * @var Damon\YouzanPay\YouzanPay
     */
    protected $app;

    /**
     * GET, CREATE
     *
     * @var string
     */
    protected $method;

    public function __construct(YouzanPay $app)
    {
        $this->app = $app;
    }

    /**
     * Send Http Request
     *
     * @param  array  $parameters
     *
     * @return array
     */
    protected function request(array $parameters = [])
    {
        $response = Zttp::get($this->getFullGateway(), $parameters);

        return $response->json();
    }

    /**
     * Get full request url.
     *
     * @return string
     */
    protected function getFullGateway()
    {
        return sprintf(self::API_GATEWAY, $this->gateway(), $this->version(), $this->getMethod(), $this->app->token->getToken());
    }

    /**
     * Get current api version
     *
     * @return string
     */
    protected function version()
    {
        return '3.0.0';
    }

    /**
     * Set method
     *
     * @param $this
     */
    protected function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    protected function getMethod()
    {
        return $this->method;
    }

    abstract protected function gateway();
}
