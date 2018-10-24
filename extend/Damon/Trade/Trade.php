<?php

namespace Damon\YouzanPay\Trade;

use Damon\YouzanPay\Support\Gateway;

class Trade extends Gateway
{
    const TRADE_WAIT_BUYER_PAY = 'WAIT_BUYER_PAY';

    const TRADE_SUCCESS = 'TRADE_SUCCESS';

    const TRADE_BUYER_SIGNED = 'TRADE_BUYER_SIGNED';

    const TRADE_CLOSED = 'TRADE_CLOSED';

    /**
     * Current trade
     *
     * @var array
     */
    protected $trade;

    protected function gateway()
    {
        return 'youzan.trade';
    }

    protected function get(array $parameters = [])
    {
        return $this->setMethod('get')->request($this->mergeParameters($parameters));
    }

    /**
     * Append default parameters
     *
     * @param  array  $parameters
     *
     * @return array
     */
    protected function mergeParameters(array $parameters)
    {
        return array_merge([
            'with_childs' => false
        ], $parameters);
    }

    /**
     * Get value from request
     *
     * @param  string  $name
     *
     * @return mixed
     */
    protected function getSourceInput($name)
    {
        $source = json_decode(file_get_contents('php://input'), true);

        return array_get($source, $name);
    }

    /**
     * Get data
     *
     * @return array
     */
    protected function getData()
    {
        $response = $this->get([
            'tid' => $this->getSourceInput('id')
        ]);

        return $this->trade = $response['response']['trade'];
    }

    /**
     * Is wait pay
     *
     * @return boolean
     */
    public function isWaitPay()
    {
        return $this->getSourceInput('status') === self::TRADE_WAIT_BUYER_PAY;
    }

     /**
     * Is successfully
     *
     * @return boolean
     */
    public function isSuccessfully()
    {
        return $this->getSourceInput('status') === self::TRADE_SUCCESS
            && in_array(array_get($this->getData(), 'status'), [self::TRADE_SUCCESS, self::TRADE_BUYER_SIGNED]);
    }

     /**
     * Is closed.
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->getSourceInput('status') === self::TRADE_CLOSED;
    }

    /**
     * Get current trade
     *
     * @return array
     */
    public function getTrade()
    {
        if (empty($this->trade)) {
            return $this->getData();
        }

        return $this->trade;
    }
}
