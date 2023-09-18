<?php
/*
$helper =new Helper();
$glob = $helper->getGlobals($_SESSION['user_id']);
*/
$key = "a9d72f3c30d91b62ae50631c65b9d4cd";
$password = "46b8b8183f5ed056b955638ec2632756";


/*
        $data = array(
            "carrier_service" => array(
                "name" => "Aramex",
                "callback_url" => "http://optilog.tk/checkout.php",
                "service_discovery" => true
        ));
        $data_string = json_encode($data);
        $ch = curl_init('https://' . $key . ':' . $password . '@baramex2.myshopify.com/admin/carrier_services.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        var_dump($result);
        die("fff");



                $ch = curl_init('https://' . $key . ':' . $password . '@devramex.myshopify.com/admin/carrier_services.json');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
                );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                $result = curl_exec($ch);
                $orders = json_decode($result, true);

                var_dump($orders);
                die("ssssssss");
      
/*

        $ch = curl_init('https://' . $key . ':' . $password . '@baramex2.myshopify.com/admin/carrier_services/14193345.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $result = curl_exec($ch);
        $orders = json_decode($result, true);

        var_dump($orders);
        die("ssssssss");
    */
$input = file_get_contents('php://input');
// parse the request
$rates = json_decode($input, true);

// log the array format for easier interpreting
//file_put_contents($filename.'-debug', print_r($rates, true));
$currency = $rates['rate']['currency'];
require_once 'Autoloader.php';
$str = trim($_GET['id']);
$str = stripslashes($str);
$id = htmlspecialchars($str);

$db = Aramex\Database::getInstance();
$arr = $db->query("SELECT settings.Services FROM settings WHERE User_id = '{$id}'")->fetchAll(\PDO::FETCH_ASSOC);

$data = unserialize($arr[0]['Services']);

$items = array();
$total_items = array();
foreach ($data as $key=>$value){
    $items['service_name'] = $key;
    $items['service_code'] = $key;
    $items['total_price'] = number_format($value, 2, '', '');
    $items['currency'] =  $currency;
    $total_items[] = $items;
}


$input = file_get_contents('php://input');
// parse the request
$rates = json_decode($input, true);
//file_put_contents($filename.'-debug', print_r($rates, true));

// total up the cart quantities for simple rate calculations
if(count($rates['rate']['items'])> 0){
    $output = array('rates' => $total_items);

	
	
	
	

/*
// build the array of line items using the prior values
$output = array('rates' => array(
    array(
        'service_name' => 'Aramex1',
        'service_code' => 'Aramex1',
        'total_price' => $overnight_cost,
        'currency' => 'USD'
    ),
    array(
        'service_name' => 'Aramex2',
        'service_code' => 'Aramex2',
        'total_price' => $regular_cost,
        'currency' => 'USD'
    )
));
*/
// encode into a json response
$json_output = json_encode($output);

// log it so we can debug the response
//($filename.'-output', $json_output);

// send it back to shopify
print $json_output;
}