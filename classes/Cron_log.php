<?php

namespace Aramex;

class Cron {

    private $helper;

    public function __construct() {
        $this->helper = new Helper();
    }

    private function formatstr($str) {
        $str = trim($str);
        $str = stripslashes($str);
        $str = htmlspecialchars($str);
        return $str;
    }

    public function cron($post = "") {
        //get users
        $users = $this->getUsers();
        foreach ($users as $user) {
            $post = array();
            $orders = $this->getOrders($user);


/////////////////
            date_default_timezone_set('Europe/Kiev');
            $date = date('m/d/Y h:i:s a', time());
            file_put_contents('./log.txt', $date. '  get New (not fulfilled orders from shopify )orders ------'. print_r($user['shop'], true) .PHP_EOL , FILE_APPEND);
////////////////


            $this->updateLastVisit($user);

            //save last orders
            $orders = array_reverse($orders);
            $idInOrdersTable = array();
            $fromTable = $this->getIdOrders($user['id']);

            foreach ($fromTable as $value) {
                foreach ($value as $value1) {
                    $idInOrdersTable[] = $value1;
                }
            }

            //save last orders
            if (count($orders['orders']) > 0) {
                foreach ($orders['orders'] as $key => $value) {
                    //add +2 hours to "paid" date
                    $time = new \DateTime($value['updated_at']);
                    $time->modify('+120minute');
                    $timeToCheck = $time->format(\DateTime::ATOM);
                    //time now
                    $nowOb = new \DateTime();
                    $now = $nowOb->format(\DateTime::ATOM);
                    //save if fresh one is not in database
                    if (!in_array($value['id'], $idInOrdersTable) && (count($value['fulfillments']) == 0) && $value['financial_status'] == 'paid') {
                        $qty = 0;
                        foreach ($value['line_items'] as $val) {
                            $qty += $val['quantity'];
                        }
                        $formattedDate = $value['created_at'];
                        $api = new Api("", "", "", "");
                        if ($user['Delay'] == "on") {
                            if ($now >= $timeToCheck) {
                                $api->saveOrder(
                                        $user['id'], $value['id'], 'Fresh', $value['name'], $value['shipping_address']['first_name'] . " " . $value['shipping_address']['last_name'], $value['shipping_address']['city'], $value['customer']['email'], $value['shipping_address']['zip'], $value['shipping_address']['country'], $value['shipping_address']['address1'] . " " . $value['shipping_address']['address2'], $value['shipping_address']['phone'], $value['shipping_address']['company'], 'Express', 'Aramex', 'SKU', $qty, $value['currency'], $value['subtotal_price'], $value['total_price'], '', '', '', $formattedDate
                                );
                            }
                        } else {
                            $api->saveOrder(
                                    $user['id'], $value['id'], 'Fresh', $value['name'], $value['shipping_address']['first_name'] . " " . $value['shipping_address']['last_name'], $value['shipping_address']['city'], $value['customer']['email'], $value['shipping_address']['zip'], $value['shipping_address']['country'], $value['shipping_address']['address1'] . " " . $value['shipping_address']['address2'], $value['shipping_address']['phone'], $value['shipping_address']['company'], 'Express', 'Aramex', 'SKU', $qty, $value['currency'], $value['subtotal_price'], $value['total_price'], '', '', '', $formattedDate
                            );
                        }
                    }
                }
            }

            $post['selectedOrders'] = array();
            $fromTableOrders = $this->getFreshOrders($user['id']);
            foreach ($fromTableOrders as $value) {
                foreach ($value as $value1) {
                    $post['selectedOrders'][] = $value1;
                }
            }


            $wsdl = new Wsdl();
            //if we have fresh orders
            if (count($post['selectedOrders']) > 0) {

/////////////////
                $date = date('m/d/Y h:i:s a', time());
                file_put_contents('./log.txt', $date. '  send Frash orders start orders creation on optilog------'. print_r(count($post['selectedOrders']), true).PHP_EOL, FILE_APPEND);
////////////////

                $wsdl->wsdlRequestMass($post, $user['id'], $user['shop'], $cron = $user['id']);



            }

/////////////////
            $date = date('m/d/Y h:i:s a', time());
            file_put_contents('./log.txt', $date. '  Start Synchronize on optilog------'. print_r($user['shop'], true).PHP_EOL, FILE_APPEND);
////////////////

            $wsdl->wsdlRequestSynchronizeMass($user['id'], $user['shop'], $cron = $user['id']);
/////////////////
            $date = date('m/d/Y h:i:s a', time());
            file_put_contents('./log.txt', $date. '  Finish Synchronize on optilog------'. print_r($user['shop'], true).PHP_EOL, FILE_APPEND);
////////////////

        }
    }

    public function stock() {
        //get users
        $users = $this->getUsersStock();
        $skus = array();

        foreach ($users as $user) {
            $skus = array();
            //just temporary array
            $temporary = array();
            //get products from Shopify e-shop
            $productsArr = $this->getProducts($user);
            foreach ($productsArr as $products) {
                foreach ($products as $product) {
                    foreach ($product['variants'] as $key => $variant) {
                        if($variant['sku'] != ""){
                        $skus[] = $variant['sku'];
                        $temporary[(string) $variant['sku']] = $variant['id'];
                        }
                    }
                }
            }
 /////////////////
                $date = date('m/d/Y h:i:s a', time());
                file_put_contents('./log.txt', print_r($temporary, true). '  get "ALL PRODUCTS FROM SHOPIFY" from Shopify ------'. print_r($user['shop'], true) .PHP_EOL , FILE_APPEND);
////////////////           
            
            
            $wsdl = new Wsdl();
            if (count($skus > 0)) {
                //get "stock level from Optilog"

                $statuses = $wsdl->wsdlRequestLevel($skus, $user['id']);
                $data = array();
/////////////////
                date_default_timezone_set('Europe/Kiev');
                $date = date('m/d/Y h:i:s a', time());
                file_put_contents('./log.txt', print_r($statuses, true). '  get "Stock level" from Optilog ------'. print_r($user['shop'], true) .PHP_EOL , FILE_APPEND);
////////////////
                //create total array with full information about "stock level from Optilog" 
                if (isset($statuses['success'])) {
                    foreach ($statuses['success'] as $key => $status) {
                       foreach ($skus as $sku) {
                            if ($status['SKU'] == $sku) {
                            $data[$key]['Id'] = $temporary[$sku];
                            $data[$key]['Available'] = $status['Available'];
                            $data[$key]['SKU'] = $status['SKU'];
                            }
                        }
                    }
                }
                
                /////////////////
                $date = date('m/d/Y h:i:s a', time());
                file_put_contents('./log.txt', print_r($data, true). '  save "WWWWWStock level" to Shopify ------' .PHP_EOL , FILE_APPEND);
////////////////
                
                
                
                //process "Order level" savinf to Shopify e-shop
/////////////////
                $date = date('m/d/Y h:i:s a', time());
                file_put_contents('./log.txt', $date. '  save "Stock level" to Shopify ------'. print_r($user['shop'], true) .PHP_EOL , FILE_APPEND);
////////////////
                foreach ($data as $levelToSave) {
                    $this->saveLevel($levelToSave, $user['shop'], $user['Key1'], $user['Password1']);
                     sleep(2);
                }
            }
        }
    }

    private function saveLevel($levelToSave, $shop, $key, $password) {

        //get current status of Item
        $data = array(
            "variant" => array(
                "id" => $levelToSave['Id'],
                "inventory_quantity" => $levelToSave['Available']
        ));

        $data_string = json_encode($data);
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/variants/' . $levelToSave['Id'] . '.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $result = json_decode(curl_exec($ch));
        
        
/////////////////
                $date = date('m/d/Y h:i:s a', time());
                file_put_contents('./log.txt', "shop" . print_r($shop, true) . " saved data ". print_r($data, true) .' Result!! save "Stock level" to Shopify ------'.print_r($result, true). "  "  .PHP_EOL , FILE_APPEND);
////////////////
        
        
    }

    private function getUsers() {
        $db = Database::getInstance();
        $arr = $db->query("SELECT * FROM settings LEFT JOIN users ON settings.User_id = users.id WHERE Auto = 'on' AND (NOW() >= Next_run_at OR Next_run_at = '') ; ")->fetchAll(\PDO::FETCH_ASSOC);
        return $arr;
    }

    private function getUsersStock() {
        $db = Database::getInstance();
        $arr = $db->query("SELECT * FROM settings LEFT JOIN users ON settings.User_id = users.id WHERE Stock = 'on' ; ")->fetchAll(\PDO::FETCH_ASSOC);
        return $arr;
    }

    private function getOrders($user) {
        $result = array();
        $glob = $this->helper->getGlobals($user['id']);
        $key = $glob['Key1'];
        $password = $glob['Password1'];

        //$user['last_visit'] =  "1980-11-01T12:19:16+00:00";
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $user['shop'] . '/admin/orders.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        return $result;
    }

    private function getProducts($user) {
        $result = array();
        $glob = $this->helper->getGlobals($user['id']);
        $key = $glob['Key1'];
        $password = $glob['Password1'];
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $user['shop'] . '/admin/products.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        return $result;
    }

    private function updateLastVisit($user) {
        date_default_timezone_set($user['TimeZone']);
        $time = new \DateTime;
        if ($user['Intervall'] == '1') {
            $int = 5;
        }
        if ($user['Intervall'] == '2') {
            $int = 15;
        }
        if ($user['Intervall'] == '3') {
            $int = 30;
        }
        if ($user['Intervall'] == '4') {
            $int = 60;
        }
        if ($user['Intervall'] == '5') {
            $int = 120;
        }
        if ($user['Intervall'] == '6') {
            $int = 240;
        }
        //from hours to minute 
        $mitute = '+' . $int . 'minute';
        $time->modify($mitute);
        $Next_run_at = $time->format(\DateTime::ATOM);
        $db = Database::getInstance();
        return $db->query("UPDATE settings SET Next_run_at = '{$Next_run_at}' WHERE User_id = '{$user['User_id']}'");
    }

    private function getIdOrders($user_id) {
        $db = Database::getInstance();
        $arr = $db->query("SELECT orders.IdOrder FROM orders  WHERE User_id = '{$user_id}'")->fetchAll(\PDO::FETCH_ASSOC);
        return $arr;
    }

    private function getFreshOrders($user_id) {
        $db = Database::getInstance();
        $arr = $db->query("SELECT orders.IdOrder FROM orders  WHERE status='Fresh' AND User_id = '{$user_id}'")->fetchAll(\PDO::FETCH_ASSOC);
        return $arr;
    }

}
