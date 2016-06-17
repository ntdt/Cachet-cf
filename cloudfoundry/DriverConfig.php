<?php


/**
 * Copyright (C) 2016 Arthur Halet
 *
 * This software is distributed under the terms and conditions of the 'MIT'
 * license which can be found in the file 'LICENSE' in this package distribution
 * or at 'http://opensource.org/licenses/MIT'.
 *
 * Author: Arthur Halet
 * Date: 16/06/2016
 */
namespace Arthurh\CloudFoundry;


use CfCommunity\CfHelper\CfHelper;

class DriverConfig
{
    /**
     * @var CfHelper
     */
    private $cfHelper;

    public function __construct()
    {
        error_reporting(ini_get("error_reporting") & ~E_NOTICE);//remove notice error
        $this->cfHelper = CfHelper::getInstance();
    }

    public function getCacheDriver()
    {
        return $this->getDriverFromEnv('CACHE_DRIVER');
    }

    private function getDriverFromEnv($envKey)
    {
        if (!$this->cfHelper->isInCloudFoundry()) {
            return env($envKey, 'file');
        }
        $this->cfHelper->getRedisConnector()->load();
        if ($this->hasRedis()) {
            return 'redis';
        }
        return env($envKey, 'file');
    }

    private function hasRedis()
    {
        $this->cfHelper->getRedisConnector()->load();
        return !empty($this->cfHelper->getRedisConnector()->getCredentials());
    }

    public function getSessionCookie()
    {
        if ($this->hasRedis()) {
            return 'laravel_cookie';
        }
        return 'JSESSIONID';
    }

    public function getSessionDriver()
    {
        return $this->getDriverFromEnv('SESSION_DRIVER');
    }
}