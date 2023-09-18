<?php

/**
 * Aramex Optilog plugin for Shopify
 * @author Aramex team
 * @version 1.0
 */

namespace Aramex;

session_start();
/**
 * include autoloader
 */
require_once 'Autoloader.php';
//general cron functionality
if (isset($_GET['cron']) && $_GET['cron'] == "1715") {
    $cron = new Cron();
    $cron->cron();

    die("done");
    exit();
}

//"stock level" cron functionality
if (isset($_GET['cron']) && $_GET['cron'] == "1716") {
    $cron = new Cron();
    $cron->stock();
    die("stock level finished");
    exit();
}

$authorization = new Authorization($_SESSION['shop']);
if (!isset($_SESSION['SecureCookie'])) {
    $authorization->createUserTable();
    //set default data to session
    $authorization->queryUserCheck();
}

//$user = $authorization->getUser($_SESSION['shop']);
$fingerprint = 'SECRETSTUFF' . $_SERVER['HTTP_USER_AGENT'];

if (isset($_SESSION['token']) && isset($_SESSION['shop']) &&  $_SESSION['fingerprint'] == md5($fingerprint . session_id())) {

    if (isset($_POST["synchronize"])) {
        $post = ($_POST) ? $_POST : "";
        $wsdl = new Wsdl();
        $response = $wsdl->wsdlRequestSynchronizeMass($_SESSION['user_id'], $_SESSION['shop']);
        print json_encode($response);
        die();
    }

    if (isset($_POST["bulk"])) {
        $post = ($_POST) ? $_POST : "";
        $wsdl = new Wsdl();
        $response = $wsdl->wsdlRequestMass($post, $_SESSION['user_id'], $_SESSION['shop']);
        print json_encode($response);
        die();
    }

    if (isset($_POST["so_token"]) && ($_SESSION['so_token'] == $_POST["so_token"])) {
        $post = ($_POST) ? $_POST : "";
        $wsdl = new Wsdl();

        $response = $wsdl->wsdlRequestSo($post, false, $_SESSION['user_id'], $_SESSION['shop']);
        print json_encode($response);
        die();
    }
    if (isset($_POST["orderin_token"]) && ($_SESSION['orderin_token'] == $_POST["orderin_token"])) {
        $post = ($_POST) ? $_POST : "";
        $wsdl = new Wsdl();
        $response = $wsdl->wsdlRequestIn($post);
        print json_encode($response);
        die();
    }

    if (isset($_POST["orderout_token"]) && ($_SESSION['orderout_token'] == $_POST["orderout_token"])) {
        $post = ($_POST) ? $_POST : "";
        $wsdl = new Wsdl();
        $response = $wsdl->wsdlRequestOut($post);
        print json_encode($response);
        die();
    }

    if (isset($_POST["ordersku_token"]) && ($_SESSION['ordersku_token'] == $_POST["ordersku_token"])) {

        $post = ($_POST) ? $_POST : "";
        $wsdl = new Wsdl();
        $response = $wsdl->wsdlRequestSku($post);
        print json_encode($response);
        die();
    }

    if (isset($_POST["settings_token"]) && ($_SESSION['settings_token'] == $_POST["settings_token"])) {
        $post = ($_POST) ? $_POST : "";
		
		if(empty($post['Interval']) && $post['Auto'] == 'on' ) {
			print json_encode(" Please enter Time interval for 'Auto mode'");
			die();
		}
		
		if(!is_numeric($post['Interval']) && $post['Auto'] == 'on') {
			print json_encode("Time interval for 'Auto mode' is not numeric");
			die();
		}

        $scs = new Api($_SESSION['shop'], $_SESSION['token'], SHOPIFY_API_KEY, SHOPIFY_SECRET);
        $response = $scs->saveSettings($post);

        if ($response === true) {
            print json_encode("Saved");
        } else {
            print json_encode("Not Saved");
        }
        die();
    }

    if (isset($_GET["logout"])) {
        $wsdl = new Wsdl();
        $response = $wsdl->logOut();
    }

    // get order from Shopify
    
    $sc = new Api($_SESSION['shop'], $_SESSION['token'], SHOPIFY_API_KEY, SHOPIFY_SECRET);
    //check if Auto mode
    //$auto = $sc->isAuto();
    $settings = $sc->getSettings();

    $settings = (isset($settings[0]))? $settings[0]: null;
    if($settings['TestMode'] == "on"){
        $_SESSION['test_mode'] = "yes";
    }
    $sc->getOrders($settings['Delay']);

    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = "";
    }

    $savedOrders = $sc->getSavedOrders($page, $_GET);


    /**
     * include html template
     */
    require 'templates/catalog.php';
} else {
    /**
     * include html template
     */
    // require_once 'templates/index.php';
    header("Location: index.php ");
    exit();
}
