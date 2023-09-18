<?php
/**
 * Aramex Optilog plugin for Shopify

 * @author Aramex team
 * @version 1.0
 */
namespace Aramex;
define('SHOPIFY_SCOPE','read_orders');
session_start();
/**
 * include autoloader
 */

require_once 'Autoloader.php';

if (isset($_GET['code'])) { // if the code param has been sent to this page... we are in Step 2
    // Step 2: do a form POST to get the access token
    $shopifyClient = new ShopifyClient($_GET['shop'], "", SHOPIFY_API_KEY, SHOPIFY_SECRET);
    session_unset();
    // Now, request the token and store it in your session.
    $_SESSION['token'] = $shopifyClient->getAccessToken($_GET['code']);

    if ($_SESSION['token'] != ''){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $_SESSION['ip'] = ip2long($ip);
        setcookie("SecureCookie", $_SESSION['ip'], time()+3600);  /* срок действия 1 час */

        $_SESSION['shop'] = $_GET['shop'];
        $fingerprint = 'SECRETSTUFF' . $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['fingerprint'] = md5($fingerprint . session_id());
        header("Location: catalog.php ");
        exit;
    }
}
// if they posted the form with the shop name
else if (isset($_POST['shop'])) {
    //else if (isset($_GET['shop'])) {
    // Step 1: get the shopname from the user and redirect the user to the
    // shopify authorization page where they can choose to authorize this app
    $shop = isset($_POST['shop']) ? $_POST['shop'] : $_GET['shop'];
    $shopifyClient = new ShopifyClient($shop, "", SHOPIFY_API_KEY, SHOPIFY_SECRET);
    // get the URL to the current page
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["SCRIPT_NAME"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["SCRIPT_NAME"];
    }
    // redirect to authorize url
    header("Location: " . $shopifyClient->getAuthorizeUrl(SHOPIFY_SCOPE, $pageURL));
    exit;
}else{
    /**
     * include html template
     */
    require_once 'templates/index.php';
}
