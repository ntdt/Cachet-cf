<?php

namespace Arthurh\CloudFoundry;

use CfCommunity\CfHelper\CfHelper;

class MailConfig
{
    private $config;
    /**
     * @var CfHelper
     */
    private $cfHelper;

    public function __construct()
    {
        error_reporting(ini_get("error_reporting") & ~E_NOTICE);//remove notice error
        $this->config = $this->getDefaultConfig();
        $this->cfHelper = CfHelper::getInstance();
    }

    private function getDefaultConfig()
    {
        return [
            'driver' => env('MAIL_DRIVER', 'smtp'),
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'from' => ['address' => env('MAIL_ADDRESS'), 'name' => env('MAIL_NAME')],
            'encryption' => 'tls',
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'sendmail' => '/usr/sbin/sendmail -bs',
            'pretend' => false,
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
        $this->loadMail();
    }

    private function loadMail()
    {
        $serviceManager = $this->cfHelper->getServiceManager();
        $smtp = $serviceManager->getService("smtp");
        if ($smtp === null) {
            $smtp = $serviceManager->getServiceByTags("smtp");
        }
        if ($smtp === null) {
            return;
        }
        $this->config['encryption'] = '';
        $this->config['sendmail'] = '';
        $this->config['host'] = $smtp->getValue("host");
        $this->config['password'] = $smtp->getValue("password");
        $this->config['username'] = $smtp->getValue("username");
        $this->config['port'] = $smtp->getValue("port");
    }
}
