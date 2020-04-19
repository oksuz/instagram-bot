<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set("Europe/Moscow");

require_once "app/config.php";
require_once "vendor/autoload.php";


use IApplication\DBService;
use IApplication\IApplication;

// exclude file name from args
$cliArgs = array_splice($argv, 1, count($argv));

if (count($cliArgs) === 0) {
    echo "usage: php cli.app [command] (follow, unfollow, mediaCrawler, initPopularAccounts)" . PHP_EOL;
    echo "follow and unfollow commands takes limit parameters." . PHP_EOL;
    echo "example: php cli.php follow 20" . PHP_EOL;
    exit(1);
}


$db = new DBService(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$application = new IApplication($db, new InstagramAPI\Instagram(INSTAGRAM_ACC, INSTAGRAM_PASS));
$application->run($cliArgs[0], array_splice($cliArgs, 1, count($cliArgs)));
