<?php

namespace IApplication;

use InstagramAPI\Instagram;

class Sync {

    /**
     * @var Instagram
     */
    private $instagram;

    /**
     * @var DBService
     */
    private $db;

    private function __construct() {}

    public static function create(Instagram $instagram, DBService $db) {
        $i = new Sync();
        $i->instagram = $instagram;
        $i->db = $db;
        ConsoleLogger::info("Synchronizer created");
        return $i;
    }


    public function followings() {
        $helper = null;
        $followings = [];
        do {
            if (is_null($helper)) {
                $helper = $this->instagram->getSelfUsersFollowing();
            } else {
                $helper = $this->instagram->getSelfUsersFollowing($helper->getNextMaxId());
            }
            $f = $helper->getFollowings();
            if (is_array($f)) {
                $followings = array_merge($followings, $f);
            }
        } while (!is_null($helper->getNextMaxId()));

        $total = 0;
        foreach ($followings as $f) {
            $result = $this->db->fetch("SELECT * FROM i_user WHERE usernameId = :usernameId AND is_active = 1", [":usernameId" => $f->getUsernameId()]);
            if (!empty($result["unfollowed"])) {
                $this->db->query("UPDATE i_user SET unfollowed = 0, unfollowed_at = NULL, scheduled_unfollow_date = NOW() WHERE usernameId = :usernameId", [":usernameId" => $f->getUsernameId()]);
                ConsoleLogger::info("user marked as unfollowed " . $result["usernameId"] . "(" . $result["username"] . ")");
                $total++;
            } else {
                $this->db->insert("i_user", ["username" => $f->getUsername(), "usernameId" => $f->getUsernameId(), "followed" => 1, "unfollowed" => 0, "is_active" => 1]);
            }
        }

        ConsoleLogger::info("total " . $total . " user marked as unfollowed");
    }


}