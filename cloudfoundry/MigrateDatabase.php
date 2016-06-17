<?php


namespace Arthurh\CloudFoundry;


use Arhframe\Util\File;
use CachetHQ\Cachet\Models\User;
use CachetHQ\Cachet\Settings\Repository;
use CfCommunity\CfHelper\CfHelper;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

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
        $this->bootSettings();
        $username = env("DEFAULT_ADMIN_USERNAME", "admin");

        $user = User::query()->where('username', '=', $username)->first();
        if (!empty($user)) {
            return;
        }
        User::create([
            'username' => $username,
            'email' => env("DEFAULT_ADMIN_EMAIL", "admin@admin.com"),
            'password' => env("DEFAULT_ADMIN_PASSWORD", "p@ssw0rd"),
            'level' => User::LEVEL_ADMIN,
            'api_key' => env("DEFAULT_ADMIN_API_KEY", "YWRtaW5wQHNzdzByZGFkbWlucGFzc3dvcmQ=")
        ]);
    }

    public function isLocked()
    {
        return $this->lockFile->isFile();
    }

    public function createLockFile()
    {
        $this->lockFile->setContent("1");
    }

    private function bootSettings()
    {
        if (Config::get('setting.app_name')) {
            return;
        }
        $setting = app(Repository::class);
        $setting->set('app_name', env('APP_NAME', 'cachet'));
        $setting->set('app_timezone', env('APP_TIMEZONE', 'UTC'));
        $setting->set('app_locale', env('APP_LOCALE', 'en'));
        $uris = (array)$this->cfHelper->getApplicationInfo()->getUris();
        $protocol = 'http://';
        if (env('USE_SSL', false)) {
            $protocol = 'https://';
        }
        $setting->set('app_domain', $protocol . $uris[0]);
    }
}
