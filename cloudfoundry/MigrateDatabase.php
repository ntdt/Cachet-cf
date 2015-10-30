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


use Arhframe\Util\File;
use CfCommunity\CfHelper\CfHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Foundation\Application;

class MigrateDatabase
{
    private static $LOCK_FILE_NAME = "dbmigrate.lock";
    /**
     * @var CfHelper
     */
    private $cfHelper;

    /**
     * @var Application
     */
    private $app;
    /**
     * @var File
     */
    private $lockFile;

    public function __construct(Application $app)
    {
        error_reporting(ini_get("error_reporting") & ~E_NOTICE);
        $this->app = $app;
        $this->cfHelper = CfHelper::getInstance();
        $this->lockFile = new File(sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::$LOCK_FILE_NAME);
    }

    public function runMigration()
    {
        if (!$this->cfHelper->isInCloudFoundry() || $this->isLocked()) {
            return;
        }
        $kernel = $this->app->make('Illuminate\Contracts\Console\Kernel');

        $status = $kernel->handle(
            $input = new ArgvInput(['artisan', 'migrate', '--force']),
            new ConsoleOutput()
        );
        $kernel->terminate($input, $status);
        $this->createLockFile();
    }

    public function isLocked()
    {
        return $this->lockFile->isFile();
    }

    public function createLockFile()
    {
        $this->lockFile->setContent("1");
    }
}
