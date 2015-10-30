<?php
/**
 * Copyright (C) 2015 Arthur Halet
 *
 * This software is distributed under the terms and conditions of the 'MIT'
 * license which can be found in the file 'LICENSE' in this package distribution
 * or at 'http://opensource.org/licenses/MIT'.
 *
 * Author: Arthur Halet
 * Date: 29/10/15
 */


namespace Arthurh\CloudFoundry;


use CfCommunity\CfHelper\CfHelper;

class DatabaseConfig
{
    private $config;

    private $sqliteFile = 'database.sqlite';

    /**
     * @var CfHelper
     */
    private $cfHelper;

    public function __construct()
    {
        error_reporting(ini_get("error_reporting") & ~E_NOTICE);//remove notice error
        $this->sqliteFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->sqliteFile;
        $this->config = $this->getDefaultConfig();
        $this->cfHelper = CfHelper::getInstance();
    }

    private function getDefaultConfig()
    {
        return [
            'fetch' => \PDO::FETCH_CLASS,
            'default' => env('DB_DRIVER', 'sqlite'),
            'connections' => [

                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => env('DB_HOST', $this->sqliteFile),
                    'prefix' => '',
                ],

                'mysql' => [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST', null),
                    'database' => env('DB_DATABASE', null),
                    'username' => env('DB_USERNAME', null),
                    'password' => env('DB_PASSWORD', null),
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                    'strict' => false,
                ],

                'pgsql' => [
                    'driver' => 'pgsql',
                    'host' => env('DB_HOST', null),
                    'database' => env('DB_DATABASE', null),
                    'username' => env('DB_USERNAME', null),
                    'password' => env('DB_PASSWORD', null),
                    'charset' => 'utf8',
                    'prefix' => '',
                    'schema' => 'public',
                ],

                'sqlsrv' => [
                    'driver' => 'sqlsrv',
                    'host' => env('DB_HOST', null),
                    'database' => env('DB_DATABASE', null),
                    'username' => env('DB_USERNAME', null),
                    'password' => env('DB_PASSWORD', null),
                    'prefix' => '',
                ],

            ],
            'migrations' => 'migrations',
            'redis' => [

                'cluster' => false,

                'default' => [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => env('REDIS_DATABASE', 0),
                ],

            ],

        ];
    }

    public function getConfig()
    {
        $this->loadFromCloudFoundry();
        return $this->config;
    }

    private function loadFromCloudFoundry()
    {
        if (!$this->cfHelper->isInCloudFoundry()) {
            return;
        }
        $this->loadDatabase();
        $this->loadRedis();
    }

    private function loadDatabase()
    {
        $this->cfHelper->getDatabaseConnector()->load();
        $credentials = $this->cfHelper->getDatabaseConnector()->getCredentials();
        if (empty($credentials)) {
            $this->initSqlite();
            return;
        }

        $type = $credentials['type'];

        $this->config['default'] = $type;
        $this->config['connections'][$type]['host'] = $credentials['host'];
        $this->config['connections'][$type]['port'] = $credentials['port'];
        $this->config['connections'][$type]['username'] = $credentials['user'];
        $this->config['connections'][$type]['password'] = $credentials['pass'];
        $this->config['connections'][$type]['database'] = $credentials['database'];
    }

    private function initSqlite()
    {
        touch($this->sqliteFile);
    }

    private function loadRedis()
    {
        $this->cfHelper->getRedisConnector()->load();
        $credentials = $this->cfHelper->getRedisConnector()->getCredentials();
        if (empty($credentials)) {
            return;
        }
        $this->config['redis']['default']['host'] = $credentials['host'];
        $this->config['redis']['default']['port'] = $credentials['port'];
        $this->config['redis']['default']['password'] = $credentials['pass'];
    }
}
