<?php

namespace IApplication;

use InstagramAPI\Instagram;

class IApplication {


    const DO_FOLLOW = "follow";
    const DO_UNFOLLOW = "unfollow";
    const INIT_POPULAR_ACCS = "initPopularAccounts";
    const DEFAULT_FOLLOW_LIMIT = 20;
    const DEFAULT_UNFOLLOW_LIMIT = 30;
    const MEDIA_CRAWLER = "mediaCrawler";
    const SYNC_FOLLOWINGS = "syncFollowings";

    private $instagram;
    private $db;

    public function __construct(DBService $dbService, Instagram $instagram) {
        $this->instagram = $instagram;
        $this->db = $dbService;
        $instagram->login();
    }

    public function run($command, $params = []) {
        switch ($command) {
            case self::INIT_POPULAR_ACCS:
                try {
                    $this->initPopularAccounts();
                } catch (\Exception $e) {
                    ConsoleLogger::error("Exception :", $e);
                    exit(1);
                }
                break;

            case self::DO_FOLLOW:
                try {
                    $this->follow($params);
                } catch (\Exception $e) {
                    ConsoleLogger::error("Exception :", $e);
                    exit(1);
                }

                break;

            case self::DO_UNFOLLOW:
                try {
                    $this->unfollow($params);
                } catch (\Exception $e) {
                    ConsoleLogger::error("Exception :", $e);
                    exit(1);
                }
                break;

            case self::MEDIA_CRAWLER:
                try {
                    $this->mediaCrawler($params);
                } catch (\Exception $e) {
                    ConsoleLogger::error("Exception :", $e);
                    exit(1);
                }
                break;

            case self::SYNC_FOLLOWINGS:
                try {
                    $sync = Sync::create($this->instagram, $this->db);
                    $sync->followings();
                } catch (\Exception $e) {
                    ConsoleLogger::error("Exception :", $e);
                    exit(1);
                }
                break;


            default:
                echo sprintf("unsupported command, available commands are [%s,%s,%s]", self::DO_UNFOLLOW, self::DO_FOLLOW, self::INIT_POPULAR_ACCS);
                exit(1);
        }
    }

    private function initPopularAccounts() {
        $records = $this->db->fetchAll("SELECT * FROM popular_accounts WHERE usernameId = :u1 OR usernameId = :u2", [":u1" => 0, ":u2" => '']);
        foreach ($records as $record) {
            $uid = $this->instagram->getUsernameId($record["username"]);
            if (!empty($uid)) {
                $this->db->query("UPDATE popular_accounts SET usernameId = :usernameId WHERE id = :id", [":usernameId" => $uid, ":id" => $record["id"]]);
                ConsoleLogger::info(sprintf("Popular account updated `%s(%s)`", $record["username"], $uid));
            }
        }
    }

    private function follow($params) {
        $service = $this->createFollowUnfollowService($params, self::DEFAULT_FOLLOW_LIMIT);
        exit($service->doFollow($params));
    }

    private function unfollow($params) {
        $service = $this->createFollowUnfollowService($params, self::DEFAULT_UNFOLLOW_LIMIT);
        exit($service->doUnfollow($params));
    }


    private function createFollowUnfollowService($params, $default) {
        if (isset($params[0]) && intval($params[0]) > 0) {
            $service = FollowUnfollowService::create($this->instagram, $this->db, intval($params[0]));
        } else {
            $service = FollowUnfollowService::create($this->instagram, $this->db, $default);
        }

        return $service;
    }

    private function mediaCrawler($params) {
        $crawler = MediaCrawler::create($this->instagram, $this->db);
        $crawler->crawl($params);
    }
}
