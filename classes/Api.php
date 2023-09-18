<?php

namespace Aramex;

class Api {

    public $shop_domain;
    private $token;
    private $api_key;
    private $secret;

    public function __construct($shop_domain, $token, $api_key, $secret) {
        $this->shop_domain = $shop_domain;
        $this->token = $token;
        $this->api_key = $api_key;
        $this->secret = $secret;
    }

    private function formatstr($str) {
        $str = trim($str);
        $str = stripslashes($str);
        $str = htmlspecialchars($str);
        $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $str = mysqli_real_escape_string($conn, $str);
        return $str;
    }

    // Get orders from Api
    public function getOrders($delay) {
        try {
            //check OrderTable
            $this->createOrderTable();
            $sc = new ShopifyClient($this->shop_domain, $this->token, $this->api_key, $this->secret);
            $orders = $sc->call('GET', '/admin/orders.json');

            //save last orders
            $orders = array_reverse($orders);
            $idInOrdersTable = array();
            $fromTable = $this->getIdOrders();
            foreach ($fromTable as $value) {
                foreach ($value as $value1) {
                    $idInOrdersTable[] = $value1;
                }
            }
            if ((count($orders)) > 0) {
                foreach ($orders as $key => $value) {
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
                        /*  if ($delay == "on") {
                          if ($now >= $timeToCheck) {

                          $this->saveOrder(
                          $_SESSION['user_id'], $value['id'], 'Fresh', $value['name'], $value['shipping_address']['first_name'] . " " . $value['shipping_address']['last_name'], $value['shipping_address']['city'], $value['customer']['email'], $value['shipping_address']['zip'], $value['shipping_address']['country'], $value['shipping_address']['address1'] . " " . $value['shipping_address']['address2'], $value['shipping_address']['phone'], $value['shipping_address']['company'], 'Express', 'Aramex', 'SKU', $qty, $value['currency'], $value['subtotal_price'], $value['total_price'], '', '', '', $formattedDate
                          );
                          }
                          /} else {
                         */

                        $this->saveOrder(
                                $_SESSION['user_id'], $this->formatstr($value['id']), 'Fresh', $this->formatstr($value['name']), $this->formatstr($value['shipping_address']['first_name']) . " " . $this->formatstr($value['shipping_address']['last_name']), $this->formatstr($value['shipping_address']['city']), $this->formatstr($value['customer']['email']), $this->formatstr($value['shipping_address']['zip']), $this->formatstr($value['shipping_address']['country']), $this->formatstr($value['shipping_address']['address1'] . " " . $value['shipping_address']['address2']), $this->formatstr($value['shipping_address']['phone']), $this->formatstr($value['shipping_address']['company']), 'Express', 'Aramex', 'SKU', $qty, $this->formatstr($value['currency']), $this->formatstr($value['subtotal_price']), $this->formatstr($value['total_price']), '', '', '', $formattedDate
                        );
                        // }
                    }
                }
            }
        } catch (ShopifyApiException $e) {
            $e->getMethod(); //-> http method (GET, POST, PUT, DELETE)
            $e->getPath(); //-> path of failing request
            $e->getResponseHeaders(); // -> actually response headers from failing request
            $e->getResponse(); //-> curl response object
            $e->getParams(); // -> optional data that may have been passed that caused the failure
        } catch (ShopifyCurlException $e) {
            echo $e->getMessage();  //returns value of curl_errno() and $e->getCode() returns value of curl_ error()
        }
    }

    private function getIdOrders() {
        $db = Database::getInstance();
        $arr = $db->query("SELECT orders.IdOrder FROM orders  WHERE User_id = '{$_SESSION['user_id']}'")->fetchAll(\PDO::FETCH_ASSOC);
        return $arr;
    }

    public function getSettings() {
        $db = Database::getInstance();
        $arr = $db->query("SELECT * FROM settings  WHERE User_id = '{$_SESSION['user_id']}' LIMIT 1")->fetchAll(\PDO::FETCH_ASSOC);
        return $arr;
    }

    /**
     * create table 'users' and 'orders' add test if table 'users', 'orders' not exist
     * @uses Database::getInstance() for access to DB
     * @orders Database::getInstance() for access to DB
     * @return void
     */
    private function createOrderTable() {
        $db = Database::getInstance();
        $db_name = DB_NAME;
        // check existing table orders
        $statement_orders = "SELECT * FROM information_schema.tables
                      WHERE table_schema = '$db_name' AND table_name = 'orders'
                      LIMIT 1;";
        $q = $db->query($statement_orders);

        // if table not exist - create table "orders"
        if (!($q->rowCount())) {
            // 1. Create table
            $db->query("CREATE TABLE orders (
                            Id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            User_id INT(6) NOT NULL,
                            IdOrder VARCHAR(255) NOT NULL,
                            Status VARCHAR(50) NOT NULL,
                            OrderNumber VARCHAR(255) NOT NULL,
                            ConsigneeName VARCHAR(255) NOT NULL,
                            ConsigneeCity VARCHAR(255) NOT NULL,
                            ConsigneeAttention VARCHAR(255) NOT NULL,
                            ConsigneeZipCode VARCHAR(255) NOT NULL,
                            ConsigneeCountryCode VARCHAR(255) NOT NULL,
                            ConsigneeAddress VARCHAR(255) NOT NULL,
                            Phone VARCHAR(255) NOT NULL,
                            Company VARCHAR(255) NOT NULL,
                            Carrier VARCHAR(50) NOT NULL,
                            ClearanceAgent VARCHAR(255) NOT NULL,
                            SKU VARCHAR(255) NOT NULL,
                            Quantity VARCHAR(255) NOT NULL,
                            Currency VARCHAR(50) NOT NULL,
                            UnitInvoicePrice VARCHAR(255) NOT NULL,
                            TotalInvoicePrice VARCHAR(255) NOT NULL,
                            Comments TEXT NOT NULL,
                            OrderReference VARCHAR(255),
                            AWB VARCHAR(255),
                            CreatedAt TIMESTAMP NOT NULL,
                            Message TEXT NOT NULL
                           )");
        }

        // check existing table "settings"
        $statement_orders = "SELECT * FROM information_schema.tables
                      WHERE table_schema = '$db_name' AND table_name = 'settings'
                      LIMIT 1;";
        $q = $db->query($statement_orders);

        // if table not exist - create table orders
        if (!($q->rowCount())) {
            // 1. Create table
            $db->query("CREATE TABLE settings (
                            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                            User_id INT(6) NOT NULL,
                            AccountEntity  VARCHAR(50) NOT NULL,
                            AccountNumber  VARCHAR(50) NOT NULL,
                            AccountPin  VARCHAR(50) NOT NULL,
                            TestMode  VARCHAR(50) NOT NULL,
                            SiteCode  VARCHAR(50) NOT NULL,
                            TimeZone  VARCHAR(50) NOT NULL,
                            Auto  VARCHAR(50) NOT NULL,
                            Key1  VARCHAR(50) NOT NULL,
                            Password1  VARCHAR(50) NOT NULL,
                            Services TEXT NOT NULL,
                            Last_run_at VARCHAR(50) NOT NULL,
                            Next_run_at VARCHAR(50) NOT NULL,
                            Intervall  VARCHAR(10) NOT NULL,
                            Delay  VARCHAR(10) NOT NULL
                            )");
        }
    }

    /**
     * save user name
     * @return void
     */
    private function updateLastVisit() {
        $helper = new Helper();
        $glob = $helper->getGlobals($_SESSION['user_id']);

        //$time = new \DateTime;
        //$currentTime = $time->format(\DateTime::ATOM);
        date_default_timezone_set($glob['TimeZone']);
        $time = new \DateTime;
        //  $time->setTimezone("Europe/Amsterdam");
        $currentTime = $time->format(\DateTime::ATOM);

        $db = Database::getInstance();
        return $db->query("UPDATE users SET last_visit = '{$currentTime}' WHERE shop = '{$this->shop_domain}'");
    }

    /**
     * save user name
     * @return void
     */
    public function saveOrder(
    $User_id, $IdOrder, $Status, $OrderNumber, $ConsigneeName, $ConsigneeCity, $ConsigneeAttention, $ConsigneeZipCode, $ConsigneeCountryCode, $ConsigneeAddress, $Phone, $Company, $Carrier, $ClearanceAgent, $SKU, $Quantity, $Currency, $UnitInvoicePrice, $TotalInvoicePrice, $Comments, $OrderReference, $AWB, $CreatedAt
    ) {
        $db = Database::getInstance();
        $db->query("INSERT INTO orders 
		(
		User_id,
		IdOrder,
		Status,
		OrderNumber,
		ConsigneeName,
		ConsigneeCity,
		ConsigneeAttention,
		ConsigneeZipCode,
		ConsigneeCountryCode,
		ConsigneeAddress,
        Phone,
        Company,
		Carrier,
		ClearanceAgent,
		SKU,
		Quantity,
		Currency,
		UnitInvoicePrice,
		TotalInvoicePrice,
		Comments,
        OrderReference,
        AWB,
        CreatedAt
		) " . "VALUES (
		'$User_id',
		'$IdOrder',
		'$Status',
		'$OrderNumber',
		'$ConsigneeName',
		'$ConsigneeCity',
		'$ConsigneeAttention',
		'$ConsigneeZipCode',
		'$ConsigneeCountryCode',
		'$ConsigneeAddress',
                '$Phone',
                '$Company',   
		'$Carrier',
		'$ClearanceAgent',
		'$SKU',
		'$Quantity',
		'$Currency',
		'$UnitInvoicePrice',
		'$TotalInvoicePrice',
		'$Comments',
         '$OrderReference',
         '$AWB',
         '$CreatedAt'
		)");
    }

    /**
     * save user name
     * @return void
     */
    private function getLastVisit() {
        $db = Database::getInstance();
        $arr = $db->query("SELECT last_visit FROM users WHERE shop = '{$_SESSION['shop']}'")->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($arr as $value) {
            return $value ['last_visit'];
        }
    }

    /**
     * get saved orders
     * @return void
     */
    public function getSavedOrders($page = 0, $get = "") {
        $per_page = LIMIT;
        $cur_page = 1;
        if ($page > 0) {
            $cur_page = $this->formatstr($page);
        }
        $start = ($cur_page - 1) * $per_page;

        //calculate number of orders
        $db = Database::getInstance();
        $indicator = array();
        if (isset($get['status']) && $get['status'] != "processed") {
            switch ($get['status']) {
                case "fresh":
                    $status = 'Fresh';
                    break;
                case "new":
                    $status = 'New';
                    break;
                case "part_allocated":
                    $status = 'Part Allocated';
                    break;
                case "allocated":
                    $status = 'Allocated';
                    break;
                case "part_picked":
                    $status = 'Part Picked';
                    break;
                case "picked":
                    $status = 'Picked';
                    break;
                case "cancelled":
                    $status = 'Cancelled';
                    break;
                case "issued":
                    $status = 'Issued';
                    break;
            }

            $sql = "SELECT Count(*) FROM orders WHERE User_id = '{$_SESSION['user_id']}' and Status = '{$status}'";
            $result = $db->prepare($sql);
            $result->execute();
            $rows = $result->fetchColumn();
            //get numbers of pages
            $num_pages = ceil($rows / $per_page);
            $arr = $db->query("SELECT * FROM orders WHERE User_id = '{$_SESSION['user_id']}' and Status = '{$status}' ORDER BY Id DESC LIMIT $start, $per_page")->fetchAll(\PDO::FETCH_BOTH);
            $indicator[] = $get['status'];
        } elseif (isset($get['status']) && $get['status'] == "processed") {
            $sql = "SELECT Count(*) FROM orders WHERE User_id = '{$_SESSION['user_id']}' and Status <> 'Fresh'";
            $result = $db->prepare($sql);
            $result->execute();
            $rows = $result->fetchColumn();
            $arr = $db->query("SELECT * FROM orders WHERE User_id = '{$_SESSION['user_id']}' and Status <> 'Fresh' ORDER BY Id DESC LIMIT $start, $per_page")->fetchAll(\PDO::FETCH_BOTH);
            $indicator[] = "processed";
        } elseif (isset($get['search'])) {
            $sql = "SELECT Count(*) FROM orders WHERE User_id = '{$_SESSION['user_id']}' and OrderNumber = '{$this->formatstr($get['search'])}'";
            $result = $db->prepare($sql);
            $result->execute();
            $rows = $result->fetchColumn();
            $arr = $db->query("SELECT * FROM orders WHERE User_id = '{$_SESSION['user_id']}' and OrderNumber = '{$this->formatstr($get['search'])}' ORDER BY Id DESC LIMIT $start, $per_page")->fetchAll(\PDO::FETCH_BOTH);
            $indicator[] = "search";
        } elseif (isset($get['time1']) || isset($get['time2'])) {
            $sql = "SELECT Count(*) FROM orders WHERE User_id = '{$_SESSION['user_id']}' and CreatedAt >= '{$this->formatstr($get['time1'])}' and CreatedAt <= '{$this->formatstr($get['time2'])} '";
            $result = $db->prepare($sql);
            $result->execute();
            $rows = $result->fetchColumn();
            $arr = $db->query("SELECT * FROM orders WHERE User_id = '{$_SESSION['user_id']}' and CreatedAt >= '{$this->formatstr($get['time1'])}' and CreatedAt <= '{$this->formatstr($get['time2'])}' ORDER BY Id DESC LIMIT $start, $per_page")->fetchAll(\PDO::FETCH_BOTH);
            $indicator[0] = "time";
            $indicator[1] = $get['time1'];
            $indicator[2] = $get['time2'];
        } else {
            $sql = "SELECT Count(*) FROM orders WHERE User_id = '{$_SESSION['user_id']}'";
            $result = $db->prepare($sql);
            $result->execute();
            $rows = $result->fetchColumn();
            $arr = $db->query("SELECT * FROM orders WHERE User_id = '{$_SESSION['user_id']}' ORDER BY Id DESC LIMIT $start, $per_page")->fetchAll(\PDO::FETCH_BOTH);
        }
        //get numbers of pages
        $num_pages = ceil($rows / $per_page);

        return array($arr, $num_pages, $cur_page, $indicator);
    }

    /**
     * save settings
     * @return void
     */
    public function saveSettings($post) {

        $Key = $this->formatstr($post['Key']);
        $Password = $this->formatstr($post['Password']);

        $data = array(
            "carrier_service" => array(
                "name" => "Aramex",
                "callback_url" => "http://optilog.tk/checkout.php?id=" . $_SESSION['user_id'],
                "service_discovery" => true
        ));
        $data_string = json_encode($data);
        $ch = curl_init('https://' . $Key . ':' . $Password . '@' . $_SESSION['shop'] . '/admin/carrier_services.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);


        $db = Database::getInstance();
        $post['TestMode'] = (isset($post['TestMode'])) ? $post['TestMode'] : "";
        $AccountEntity = $this->formatstr($post['AccountEntity']);
        $AccountNumber = $this->formatstr($post['AccountNumber']);
        $AccountPin = $this->formatstr($post['AccountPin']);
        $TestMode = $this->formatstr(isset($post['TestMode']) ? $post['TestMode'] : "");
        $Interval = $this->formatstr($post['Interval']);
        $Delay = $this->formatstr(isset($post['Delay']) ? $post['Delay'] : "" );
        $Stock = $this->formatstr(isset($post['Stock']) ? $post['Stock'] : "" );
        if (trim($TestMode) == "") {
            $_SESSION['test_mode'] = "";
        } else {
            $_SESSION['test_mode'] = "yes";
        }

        $post['Auto'] = (isset($post['Auto'])) ? $post['Auto'] : "";
        $Auto = $this->formatstr($post['Auto']);
        if (trim($Auto) == "") {
            $Auto = '';
        } else {
            $Auto = $this->formatstr($post['Auto']);
        }

        $SiteCode = $this->formatstr($post['SiteCode']);
        $TimeZone = $this->formatstr($post['TimeZone']);
        $user_id = $_SESSION['user_id'];
        $sql = 'SELECT Count(*) FROM settings WHERE User_id = "' . $_SESSION['user_id'] . '"';
        $result = $db->prepare($sql);
        $result->execute();
        $rows = $result->fetchColumn();

        $services = array();
        foreach ($post as $key => $post1) {
            if (strpos($key, "Service") !== false) {
                $index = preg_replace("/[^0-9]/", "", $key);
                if (trim($post['Price' . $index]) !== "") {
                    $services[$post1] = $post['Price' . $index];
                }
            }
        }
        $Services = serialize($services);
        $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($rows > 0) {
            $result = $db->query('UPDATE settings SET User_id = "' . $user_id . '", AccountEntity = "' . $AccountEntity . '", AccountNumber = "' . $AccountNumber . '", '
                    . 'AccountPin = "' . $AccountPin . '", TestMode = "' . $TestMode . '", SiteCode = "' . $SiteCode . '",
                TimeZone = "' . $TimeZone . '", Auto = "' . $Auto . '",
                Key1 = "' . $Key . '", Password1 = "' . $Password . '", Services = "' . mysqli_real_escape_string($conn, $Services) . '", Intervall = "' . $Interval . '", Delay = "' . $Delay . '", Stock = "' . $Stock . '"
                 WHERE  User_id = "' . $_SESSION['user_id'] . '" 
                ');
        } else {
            $result = $db->query("INSERT INTO settings (User_id, AccountEntity, AccountNumber, AccountPin, TestMode, SiteCode, TimeZone, Auto, Key1, Password1, Services) " . "VALUES ('$user_id','$AccountEntity', '$AccountNumber', '$AccountPin', '$TestMode','$SiteCode', '$TimeZone', '$Auto', '$Key', '$Password', '$Services')");
        }
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}

?>
