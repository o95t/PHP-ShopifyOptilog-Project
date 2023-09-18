<?php

namespace Aramex;

class Wsdl {

    private $helper;

    public function __construct() {
        $this->helper = new Helper();
    }

    private function formatstr($str) {
        $str = trim($str);
        $str = stripslashes($str);
        $str = htmlspecialchars($str);
        $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $str = mysqli_real_escape_string($conn, $str);
        return $str;
    }

    public function wsdlRequestSynchronizeMass($user_id, $shop, $cron = false) {
        $db = Database::getInstance();
        //$imploded = implode(',', $post['selectedOrders']);
        $arr = $db->query("SELECT * FROM orders  WHERE Status NOT IN ('Fresh', 'Issued') and User_id= '{$user_id}' ORDER BY Id DESC")->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();

        foreach ($arr as $key => $value) {
            $OrderReference = $value['OrderReference'];
            $res = $this->wsdlRequestStatus($OrderReference, $cron);
            if ($res["success"]) {
                $status = $res['success']['OrderStatus'];
                switch ($status) {
                    case -3:
                        $status = 'Cancelled';
                        break;
                    case -2:
                        $status = 'Issued';
                        break;
                    case -1:
                        $status = 'Invoiced';
                        break;
                    case 0:
                        $status = 'New';
                        break;
                    case 1:
                        $status = 'Part Allocated';
                        break;
                    case 2:
                        $status = 'Allocated';
                        break;
                    case 3:
                        $status = 'Staged';
                        break;
                    case 4:
                        $status = 'Part Picked';
                        break;
                    case 5:
                        $status = 'Picked';
                        break;
                    case 6:
                        $status = 'Part Loaded';
                        break;
                    case 7:
                        $status = 'Loaded';
                        break;
                    case 8:
                        $status = 'Part Shipped';
                        break;
                    case 9:
                        $status = 'Shipped';
                        break;
                }
          
                $AWBNumber = ($res['success']['AWBNumber']) ? $res['success']['AWBNumber'] : "none";
                $totalIssuedItems = array();

                if ($res['success']['OrderStatus'] == -2) {
                    $totalLineItems = $this->getTotalItemsInfo($value['IdOrder'], $shop, $cron);
                }

                if (is_object($res['success']['Items']->KeyValueOfstringstring) && $res['success']['OrderStatus'] == -2) {
                    if ($totalLineItems == $res['success']['Items']->KeyValueOfstringstring->Value) {
                        // if all lineitems were fulfilled
                        $this->awbShopify($value['IdOrder'], $AWBNumber, $shop, $cron);
                    } else {
                        $this->partialFullfilled($AWBNumber, $value['IdOrder'], $res['success']['Items']->KeyValueOfstringstring->Key, $res['success']['Items']->KeyValueOfstringstring->Value, $shop, $cron);
                    }
                } else {
                    if ($res['success']['OrderStatus'] == -2) {
                        $total = 0;
                        foreach ($res['success']['Items']->KeyValueOfstringstring as $listItem) {
                            $total = $total + $listItem->Value;
                        }
                        if ($totalLineItems == $total) {
                            $this->awbShopify($value['IdOrder'], $AWBNumber, $shop, $cron);
                        } else {
                            foreach ($res['success']['Items']->KeyValueOfstringstring as $listItem) {
                                $this->partialFullfilled($AWBNumber, $value['IdOrder'], $listItem->Key, $listItem->Value, $shop, $cron);
                            }
                        }
                    }
                }

                if (isset($res['success']['OrderStatus'])) {

                    //save Status to Shopify
                    $this->statusShopify($value['IdOrder'], $status, $shop, $cron);

                    //save Canceled to Shopify
                    if ($status == 'Cancelled') {
                        $this->saveCanceled($value['IdOrder'], $status, $shop, $cron);
                    }
                    

                    $db->query('UPDATE orders SET status = "' . $status . '", AWB =  "' . $res['success']['AWBNumber'] . '" WHERE IdOrder = "' . $value['IdOrder'] . '" AND  User_id = "' . $user_id . '"');

                    $result['status'][] = $status;
                    $result['id'][] = $value['IdOrder'];
                    $result['awb'][] = $res['success']['AWBNumber'];
                }

                if (isset($res['success']['AWBNumber']) && $res['success']['AWBNumber'] !== "") {
                    
                } else {
                    $AWBNumber = "none";
                }
                $result['message'] .= "# " . $value['OrderNumber'] . " AWB Number: " . $AWBNumber . ", Status: " . $status . "<br />";
            } else {
                $result['message'] .= "# " . $value['OrderNumber'] . " Not processed order<br />";
            }
        }
        return $result;
    }

    private function getTotalItemsInfo($orederNumber, $shop, $cron = false) {

        if ($cron === false) {
            $glob = $this->helper->getGlobals($_SESSION['user_id']);
        } else {
            $glob = $this->helper->getGlobals($cron);
        }

        $key = $glob['Key1'];
        $password = $glob['Password1'];

        //get current status of Item
        $data = array(
            "order" => array(
                "id" => $orederNumber
        ));

        $data_string = json_encode($data);
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/orders/' . $orederNumber . '.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $result = json_decode(curl_exec($ch));
        $total = 0;
        foreach ($result->order->line_items as $item) {

            $total = $total + $item->quantity;
        }
        return $total;
    }

    public function wsdlRequestStatus($OrderReference = "", $cron = false) {
        if ($cron === false) {
            $glob = $this->helper->getGlobals($_SESSION['user_id']);
        } else {
            $glob = $this->helper->getGlobals($cron);
        }
        $params = array('Request' =>
            array(
                'AccountDetails' => array(
                    'AccountEntity' => $glob['AccountEntity'],
                    'AccountNumber' => $glob['AccountNumber'],
                    'AccountPin' => $glob['AccountPin'],
                    'SiteCode' => $glob['SiteCode']
                ),
                'OrderReference' => $OrderReference
            ),
        );
        return $this->processWsdlStatus($params);
    }

    private function processWsdlStatus($params) {

        $baseUrl = $this->getWsdlPath();
        $soapClient = new \SoapClient($baseUrl . 'OptilogAPI.WSDL', array('trace' => 1, 'keep_alive' => false));

        try {
            $results = $soapClient->GetSOStatus($params);
            if ($results->GetSOStatusResult->HasErrors) {
                $response['error'] = ' Aramex: ' . $results->CreateSOResult->ErrorDescription;
            } else {
                $response['success']['AWBNumber'] = $results->GetSOStatusResult->AWBNumber;
                $response['success']['OrderStatus'] = $results->GetSOStatusResult->OrderStatus;
                $response['success']['Items'] = $results->GetSOStatusResult->Items;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return $response;
    }

    private function statusShopify($orederNumber, $note, $shop, $cron = false) {
        if ($cron === false) {
            $glob = $this->helper->getGlobals($_SESSION['user_id']);
        } else {
            $glob = $this->helper->getGlobals($cron);
        }

        $key = $glob['Key1'];
        $password = $glob['Password1'];
        $data = array(
            "order" => array(
                "id" => $orederNumber,
                "note" => $note
        ));

        $data_string = json_encode($data);
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/orders/' . $orederNumber . '.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $result = curl_exec($ch);
        return $result;
    }

    private function saveCanceled($orederNumber, $note, $shop, $cron = false) {
        if ($cron === false) {
            $glob = $this->helper->getGlobals($_SESSION['user_id']);
        } else {
            $glob = $this->helper->getGlobals($cron);
        }
        $key = $glob['Key1'];
        $password = $glob['Password1'];
        $data = array(
            "order" => array(
                "note" => "Canceled from Aramex side"
        ));
        $data_string = json_encode($data);
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/orders/' . $orederNumber . '/cancel.json');

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
    }

    private function partialFullfilled($AWBNumber, $orederNumber, $sku, $qty, $shop, $cron = false) {
        if ($cron === false) {
            $glob = $this->helper->getGlobals($_SESSION['user_id']);
        } else {
            $glob = $this->helper->getGlobals($cron);
        }
        $key = $glob['Key1'];
        $password = $glob['Password1'];

        //get current status of Item
        $data = array(
            "order" => array(
                "id" => $orederNumber
        ));

        $data_string = json_encode($data);
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/orders/' . $orederNumber . '.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $result = json_decode(curl_exec($ch));

        foreach ($result->order->line_items as $item) {
            if ($item->sku == $sku) {
                $marker = $item->quantity - $item->fulfillable_quantity;

                if ($marker == 0) {
                    $quantity_save = $qty;
                }
                if ($marker == $qty) {
                    $quantity_save = 0;
                }
                if ($qty > $marker) {
                    $quantity_save = $qty - $marker;
                }

                $data = array(
                    "fulfillment" => array(
                        "tracking_number" => $AWBNumber,
                        "tracking_url" => 'https://www.aramex.com/track/results?mode=0&ShipmentNumber=' . $AWBNumber,
                        "notify_customer" => true,
                        "carrier" => "Aramex",
                        "tracking_company" => "Aramex",
                        'line_items' => array(
                            array(
                                'id' => $item->id,
                                'tracking_url' => $item->id,
                                "fulfillment_service" => "Aramex",
                                "carrier" => "Aramex",
                                "fulfillment_status" => "fulfilled",
                                "quantity" => ($quantity_save) ? $quantity_save : 0
                            )
                        )
                ));
                $data_string = json_encode($data);
                $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/orders/' . $orederNumber . '/fulfillments.json');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
                );
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                if ($item->quantity == $qty) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    private function awbShopify($orederNumber, $tracking_number, $shop, $cron = false) {
        if ($cron === false) {
            $glob = $this->helper->getGlobals($_SESSION['user_id']);
        } else {
            $glob = $this->helper->getGlobals($cron);
        }
        $key = $glob['Key1'];
        $password = $glob['Password1'];

        $data = array(
            "fulfillment" => array(
                "tracking_number" => $tracking_number,
                "tracking_company" => "Aramex",
                "carrier" => "Aramex",
                "tracking_url" => 'https://www.aramex.com/track/results?mode=0&ShipmentNumber=' . $tracking_number,
                "notify_customer" => true
        ));
        $data_string = json_encode($data);
        $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/orders/' . $orederNumber . '/fulfillments.json');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        return $result;
    }

    public function wsdlRequestMass($post = "", $user_id, $shop, $cron = false) {
        $db = Database::getInstance();
        $imploded = $this->formatstr(implode(',', $post['selectedOrders']));
        $arr = $db->query("SELECT * FROM orders WHERE IdOrder IN ({$imploded}) and User_id= '{$user_id}' ")->fetchAll(\PDO::FETCH_ASSOC);
        $result = array();
        $message = "";
        foreach ($arr as $key => $value) {
            $mass = $value['IdOrder'];
            $res = $this->wsdlRequestSo($value, $mass, $user_id, $shop, $cron);
            if (!$res["success"]) {
                $res["success"] = $res["error"];
            }
            $message .= $value['OrderNumber'] . " " . $res["success"] . "<br />";
            $result['message'] = $message;
            if ($res['data_order_IdOrder'] && (!isset($res["error"]))) {
                $result['orders'][] = $res['data_order_IdOrder'];
            }
        }
        return $result;
    }

    public function wsdlRequestSo($post = "", $mass = "", $user_id, $shop, $cron = false) {
        $response = array();
        if ($post) {
            if (empty($post)) {
                $response['error'] = 'Invalid form data.';
                print json_encode($response);
                die();
            } else {
                $Id_order = $post['Id_order'];
                if (!$Id_order) {
                    $Id_order = $mass;
                }
                $Id_order = $this->formatstr($Id_order);
                $db = Database::getInstance();
                $arr = $db->query("SELECT status FROM orders WHERE IdOrder= '{$Id_order}' and User_id= '{$user_id}'")->fetchAll(\PDO::FETCH_ASSOC);
                if ($arr[0]['status'] == 'Processed') {
                    $response['error'] = 'Aramex: This order already processed';
                    return $response;
                }
                if ($arr[0]['status'] == 'Blocked') {
                    $response['error'] = 'Aramex: This order blocked';
                    return $response;
                }
                if ($arr[0]['status'] !== 'Fresh') {
                    $response['error'] = 'Aramex: This order already processed';
                    return $response;
                }
                $result = array();
                if ($cron === false) {
                    $glob = $this->helper->getGlobals($_SESSION['user_id']);
                } else {
                    $glob = $this->helper->getGlobals($cron);
                }
                $key = $glob['Key1'];
                $password = $glob['Password1'];

                $ch = curl_init('https://' . $key . ':' . $password . '@' . $shop . '/admin/orders.json?ids=' . $Id_order . '');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
                );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                $result = curl_exec($ch);
                $orders = json_decode($result, true);

                if (isset($orders["errors"])) {
                    return false;
                }

                $item = array();
                foreach ($orders['orders'][0]['line_items'] as $key => $product) {

                    $item['ShippingOrderItem'][$key]['SKU'] = $product['sku'];
                    $item['ShippingOrderItem'][$key]['UnitCost'] = $product['price'];
                    $item['ShippingOrderItem'][$key]['Comments'] = "";
                    $item['ShippingOrderItem'][$key]['Quantity'] = $product['quantity'];
                    $item['ShippingOrderItem'][$key]['Reference1'] = $this->formatstr($post['Currency']) ? $this->formatstr($post['Currency']) : "";
                    $item['ShippingOrderItem'][$key]['Reference2'] = $product['price'];
                    $item['ShippingOrderItem'][$key]['Reference3'] = $product['price'] * $product['quantity'];
                    $item['ShippingOrderItem'][$key]['Reference4'] = $orders['orders'][0]['shipping_lines'][0]['price'] ? $orders['orders'][0]['shipping_lines'][0]['price'] : "";
                }

                $params = array('Request' =>
                    array(
                        'AccountDetails' => array(
                            'AccountEntity' => (string) $glob['AccountEntity'],
                            'AccountNumber' => (string) $glob['AccountNumber'],
                            'AccountPin' => (string) $glob['AccountPin'],
                            'SiteCode' => (string) $glob['SiteCode'],
                        ),
                        'SO' => array(
                            'Bonded' => 1,
                            'BondedType' => 2,
                            'OrderNumber' => $this->formatstr($post['OrderNumber']) ? $this->formatstr($post['OrderNumber']) : "",
                            'ConsigneeName' => $this->formatstr($post['ConsigneeName']) ? $this->formatstr($post['ConsigneeName']) : "",
                            'ConsigneeCity' => $this->formatstr($post['ConsigneeCity']) ? $this->formatstr($post['ConsigneeCity']) : "",
                            'ConsigneeAttention' => $this->formatstr($post['ConsigneeAttention']) ? $this->formatstr($post['ConsigneeAttention']) : "",
                            'ConsigneeZipCode' => $this->formatstr($post['ConsigneeZipCode']) ? $this->formatstr($post['ConsigneeZipCode']) : "",
                            'ConsigneeCountryCode' => $this->formatstr($post['ConsigneeCountryCode']) ? $this->formatstr($post['ConsigneeCountryCode']) : "",
                            'ConsigneeAddress' => $this->formatstr($post['ConsigneeAddress']) ? $this->formatstr($post['ConsigneeAddress']) : "",
                            'Carrier' => $orders['orders'][0]['shipping_lines'][0]['title'] ? $orders['orders'][0]['shipping_lines'][0]['title'] : "",
                            'ClearanceAgent' => $this->formatstr($post['ClearanceAgent']) ? $this->formatstr($post['ClearanceAgent']) : "",
                            'ConsigneeReference' => $this->formatstr($post['ConsigneeCompany']) ? $this->formatstr($post['ConsigneeCompany']) : "",
                            'ConsigneePhone' => $this->formatstr($post['Phone']) ? $this->formatstr($post['Phone']) : "",
                            'Items' => $item
                        )
                    ),
                );
                return $this->processWsdlSo($params, 'CreateSO', $Id_order, $user_id);
            }
        }
    }

    private function processWsdlSo($params, $fun, $Id_order, $user_id) {
        $baseUrl = $this->getWsdlPath();
        $soapClient = new \SoapClient($baseUrl . 'OptilogAPI.WSDL', array('trace' => 1, 'keep_alive' => false));
        try {
            $results = $soapClient->$fun($params);
            $db = Database::getInstance();
            if ($results->CreateSOResult->HasErrors) {
                $response['error'] = 'Aramex: ' . $results->CreateSOResult->ErrorDescription;
                if (strpos($results->CreateSOResult->ErrorDescription, "not found")) {
                    $db->query('UPDATE orders SET status = "Blocked", Message =  "' . $results->CreateSOResult->ErrorDescription . '" WHERE IdOrder = "' . $Id_order . '" AND  User_id = "' . $user_id . '"');
                    $arr = $db->query("SELECT orders.OrderNumber FROM orders  WHERE IdOrder = {$Id_order} AND User_id = '{$user_id}'")->fetchAll(\PDO::FETCH_ASSOC);
                    $OrderNumber = $arr[0]["OrderNumber"];
                    $this->processCancel($OrderNumber, $user_id);
                }
            } else {

                if ($results->CreateSOResult->OrderReference) {
                    $OrderReference = $results->CreateSOResult->OrderReference;
                } else {
                    $OrderReference = "";
                }
                $db->query('UPDATE orders SET status = "Processed", OrderReference =  "' . $OrderReference . '" WHERE IdOrder = "' . $Id_order . '" AND  User_id = "' . $user_id . '"');
                $response['success'] = 'Aramex: OrderReference - <b>' . $results->CreateSOResult->OrderReference . '</b>';
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        $response['data_order_IdOrder'] = $Id_order;
        return $response;
    }

    private function processCancel($OrderNumber, $cron = false) {
        if ($cron === false) {
            $glob = $this->helper->getGlobals($_SESSION['user_id']);
        } else {
            $glob = $this->helper->getGlobals($cron);
        }
        $params = array('Request' =>
            array(
                'AccountDetails' => array(
                    'AccountEntity' => $glob['AccountEntity'],
                    'AccountNumber' => $glob['AccountNumber'],
                    'AccountPin' => $glob['AccountPin'],
                    'SiteCode' => $glob['SiteCode']
                ),
                'OrderNumbers' => $OrderNumber
            ),
        );
        return $this->Cancel($params);
    }

    private function Cancel($params) {
        $baseUrl = $this->getWsdlPath();
        $soapClient = new \SoapClient($baseUrl . 'OptilogAPI.WSDL', array('trace' => 1, 'keep_alive' => false));
        try {
            $results = $soapClient->CancelSO($params);
            if ($results->CancelSOResult->HasErrors) {
                $response['error'] = ' Aramex: ' . $results->CancelSOResult->ErrorDescription;
            } else {
                
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return $response;
    }

    private function getWsdlPath() {
        $wsdlBasePath = $_SERVER['DOCUMENT_ROOT'] . "/media/";
        if ($_SESSION['test_mode'] == "yes") {
            $wsdlBasePath .= 'TestMode/';
        }
        return $wsdlBasePath;
    }

    public function wsdlRequestLevel($SKUs, $user_id) {
        $glob = $this->helper->getGlobals($user_id);
        foreach ($SKUs as $key => $value) {
            $SKUs[$key] = $this->formatstr($value);
        }
        $params = array('Request' =>
            array(
                'AccountDetails' => array(
                    'AccountEntity' => $glob['AccountEntity'],
                    'AccountNumber' => $glob['AccountNumber'],
                    'AccountPin' => $glob['AccountPin'],
                    'SiteCode' => $glob['SiteCode']
                ),
                'SKUs' => $SKUs
            )
        );
        return $this->processWsdlLevel($params, 'GetStock');
    }

    private function processWsdlLevel($params, $fun) {
        $baseUrl = $this->getWsdlPath();
        $soapClient = new \SoapClient($baseUrl . 'OptilogAPI.WSDL', array('trace' => 1,));
        try {
            $results = $soapClient->$fun($params);
            if ($results->GetStockResult->HasErrors) {
                $response['error'] = '' . $results->GetStockResult->ErrorDescription;
            } else {
                $message = Array();
                foreach ($results->GetStockResult->ItemsStock as $key => $item) {
                    if (count($item) == 1) {
                        $message[$key]['Available'] = $item->Available;
                        $message[$key]['SKU'] = $item->SKU;
                    } else {
                        foreach ($item as $key => $item1) {
                            $message[$key]['Available'] = $item1->Available;
                            $message[$key]['SKU'] = $item1->SKU;
                        }
                    }
                }
                $response['success'] = $message;
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }
        return $response;
    }

    public function wsdlRequestSku($post) {
        $response = array();
        if ($post) {
            if (empty($post)) {
                $response['error'] = 'Invalid form data.';
            } else {
                $SKUs = array_values($post['SKUs']);
                foreach ($SKUs as $key => $value) {
                    $SKUs[$key] = $this->formatstr($value);
                }
                $params = array('Request' =>
                    array(
                        'AccountDetails' => array(
                            'AccountEntity' => ACCOUNTENTITY,
                            'AccountNumber' => ACCOUNTNUMBER,
                            'AccountPin' => ACCOUNTPIN,
                            'SiteCode' => SITECODE
                        ),
                        'SKUs' => $SKUs
                    )
                );
                return $this->processWsdlSku($params, 'GetStock');
            }
        }
    }

    public function logOut() {
        session_destroy();
        header("Location: index.php ");
        die();
    }

}
