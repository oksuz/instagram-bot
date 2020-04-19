<?php

namespace IApplication;


use InstagramAPI\Instagram;

class FollowUnfollowService {

    /**
     * @var Instagram $instagram
     */
    private $instagram;

    /**
     * @var int $limit
     */
    private $limit;

    /**
     * @var DBService
     */
    private $db;

    /**
     * @var int $requestCounter
     */
    private $requestCounter;

    private function __construct() {}

    public static function create(Instagram $instagram, DBService $db, $limit) {
        $instance = new FollowUnfollowService();
        $instance->instagram = $instagram;
        $instance->limit = $limit;
        $instance->requestCounter = 0;
        $instance->db = $db;
        ConsoleLogger::info("FollowUnfollowService initialized with limit:" . $limit);
        return $instance;
    }

    private function followOrUnfollow($usernameId, $method) {
        if (empty($usernameId)) {
            throw new \Exception("cannot produce unfollow request because usernameId is null", 1);
        }


        if ($this->incrementCounterAndGet() > $this->limit) {
            throw new \Exception(sprintf("%s request limit %d reached. quiting", $method, $this->limit), 0);
        }


        $response = $this->instagram->$method($usernameId);
        ConsoleLogger::info(sprintf("user %s", $method), [$usernameId, $response]);

        if (!empty($response["status"]) && $response["status"] === "fail") {
            $exMsg = isset($response["message"]) ? $response["message"] : "something went wrong";
            throw new \Exception($exMsg, 1);
        }

    }


    private function incrementCounterAndGet() {
        return ++$this->requestCounter;
    }

    public function doFollow($params) {
        $list = $this->db->fetchAll("SELECT * FROM i_user WHERE followed = :f ", [":f" => 0]);
        foreach ($list as $u) {
            try {
                $response = $this->followOrUnfollow($u["usernameId"], 'follow');
                $this->db->query("UPDATE i_user SET followed = :u1, followed_at = :u2, scheduled_unfollow_date = :u3 WHERE id = :id",
                    [':id' => $u["id"], ":u1" => 1, ":u2" => date("Y-m-d H:i:s"), ":u3" => date('Y-m-d H:i:s', strtotime('+2 days', time()))]);
            } catch (\Exception $e) {
                ConsoleLogger::error("follow error ", $e);
                return $e->getCode();
            }
        }

        return 0;
    }

    public function doUnfollow($params) {
        $list = $this->db->fetchAll("SELECT * FROM i_user WHERE followed = :f AND unfollowed = :u AND scheduled_unfollow_date < NOW() ORDER BY scheduled_unfollow_date ASC", [":u" => 0, ":f" => 1]);
        ConsoleLogger::info("Unfollow size " . count($list));
        foreach ($list as $u) {
            try {
                $response = $this->followOrUnfollow($u["usernameId"], 'unfollow');
                $this->db->query("UPDATE i_user SET unfollowed = :u1, unfollowed_at = :u2 WHERE id = :id", [':id' => $u["id"], ":u1" => 1, ":u2" => date("Y-m-d H:i:s")]);
            } catch (\Exception $e) {
                ConsoleLogger::error("unfollow error ", $e);
                return $e->getCode();
            }
        }

        return 0;
    }
}
