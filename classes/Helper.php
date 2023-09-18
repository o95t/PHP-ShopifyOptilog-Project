<?php

namespace Aramex;

class Helper  {
    public function getGlobals($user_id) {
        $db = Database::getInstance();
        $arr = $db->query("SELECT * FROM settings WHERE User_id = '{$user_id}' ")->fetchAll(\PDO::FETCH_ASSOC);
        return $arr[0];
    }
}
