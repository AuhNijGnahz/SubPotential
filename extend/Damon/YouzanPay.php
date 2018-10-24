<?php

namespace Damon;

use Pimple\Container;
use Damon\Core\Token;
use Damon\YouzanPay\Trade\Trade;
use Doctrine\Common\Cache\Cache;
use Illuminate\Config\Repository;
use Damon\QrCode\QrCode;
use Doctrine\Common\Cache\FilesystemCache;

class YouzanPay extends Container
{
    public function __construct(array $config)
    {
        parent::__construct();

        $this->loadConfig($config);

        $this->setCacheDriver(new FilesystemCache(sys_get_temp_dir()));

        $this->registerCoreService();

        $this->registerServices();
    }

    /**
     * Register Config Repository
     *
     * @param  array  $config
     *
     * @return void
     */
    protected function loadConfig($config)
    {
        $this['config'] = function () use ($config) {
            return new Repository($config);
        };
    }

    /**
     * Set Cache driver
     *
     * @param Doctrine\Common\Cache\Cache $cache
     */
    protected function setCacheDriver(Cache $cache)
    {
        $this['cache'] = $cache;
    }

    /**
     * Register Core Services
     *
     * @return void
     */
    protected function registerCoreService()
    {
        $this['token'] = function () {
            return new Token($this['config']['client_id'],
                            $this['config']['client_secret'],
                            $this['config']['store_id'],
                            $this['cache']
                        );
        };
    }

    /**
     * Register services
     *
     * @return void
     */
    protected function registerServices()
    {
        $this['qrcode'] = function () {
            return new QrCode($this);
        };

        $this['trade'] = function () {
            return new Trade($this);
        };
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return $this[$name];
    }
}
