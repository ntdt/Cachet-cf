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

class PathConfig
{
    const BASE_PATH = '/home/vcap/app';
    /**
     * @var CfHelper
     */
    private $cfHelper;

    public function __construct()
    {
        error_reporting(ini_get("error_reporting") & ~E_NOTICE);//remove notice error
        $this->cfHelper = CfHelper::getInstance();
        $this->getPathInCloudFoundry('bootstrap', 'cachet');
    }

    private function getPathInCloudFoundry($type, $path)
    {
        $finalPath = PathConfig::BASE_PATH . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $path;
        $relativePath = __DIR__ . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $path;
        if (!is_file($relativePath) && !is_dir($relativePath)) {
            mkdir($relativePath, 0777, true);
        }
        return $finalPath;
    }

    public function getDatabasePath($path)
    {
        if ($this->cfHelper->isInCloudFoundry()) {

            return $this->getPathInCloudFoundry('database', $path);
        }
        return database_path($path);
    }

    public function getStoragePath($path)
    {
        if ($this->cfHelper->isInCloudFoundry()) {
            return $this->getPathInCloudFoundry('storage', $path);
        }
        return realpath(storage_path($path));
    }

    public function getBasePath($path)
    {
        if ($this->cfHelper->isInCloudFoundry()) {
            return PathConfig::BASE_PATH . DIRECTORY_SEPARATOR . $path;
        }
        return realpath(base_path($path));
    }
}