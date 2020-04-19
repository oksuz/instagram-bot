<?php

namespace IApplication;


use InstagramAPI\Instagram;
use InstagramAPI\Item;
use InstagramAPI\User;

class MediaCrawler {

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
        $i = new MediaCrawler();
        $i->instagram = $instagram;
        $i->db = $db;
        return $i;
    }

    public function crawl($params) {
        $accounts = $this->db->fetchAll("SELECT * FROM popular_accounts WHERE usernameId != 0 AND usernameId IS NOT NULL");
        foreach ($accounts as $acc) {
            $this->crawlUserFeed($acc);
        }
    }

    private function crawlUserFeed($acc) {
        $userFeed = $this->instagram->getUserFeed($acc["usernameId"]);
        $items = $userFeed->getItems();
        if (empty($items)) {
            ConsoleLogger::info("User feed is empty " . $acc["username"]);
            return;
        }

        /** @var Item $item */
        $item = $items[0];
        $likers = $this->instagram->getMediaLikers($item->getMediaId());
        if (empty($likers->getLikers())) {
            ConsoleLogger::info("Media likers are empty " . $acc["username"] . " " . $item->getMediaId());
            return;
        }

        if (false === $this->db->fetch("SELECT * FROM i_media WHERE media_id = :mid", [":mid" => $item->getMediaId()])) {
            $this->db->insert("i_media", ["popular_acc_id" => $acc["id"], "media_id" => $item->getMediaId()]);
        }

        $counter = 0;
        /** @var User $user */
        foreach ($likers->getLikers() as $user) {
            $u = $this->db->fetch("SELECT * FROM i_user WHERE usernameId = :uid", [":uid" => $user->getUsernameId()]);
            if (false === $u) {
                $this->db->insert("i_user", ["username" => $user->getUsername(), "usernameId" => $user->getUsernameId()]);
                $counter++;
            }
        }

        ConsoleLogger::info(sprintf("%d user inserted for media Id(%s) of user %s", $counter, $item->getMediaId(), $acc["username"]));

    }

}