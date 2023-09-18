<?php
/**
 * database connection settings
 */

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

define("LIMIT", "3");

 /**
 * server
 */
define("DB_SERVER", "localhost");

/**
 * username to Database
 */
define("DB_USERNAME", "root");

/**
 * password
 */
define("DB_PASSWORD", "");
//define("DB_PASSWORD", "1715Qwerty");


/**
 * Database name
 */
define("DB_NAME", "test");
//define("DB_NAME", "shopify");


/**
 *  Define your APP`s key and secret
 */
define('SHOPIFY_API_KEY','be37a15c6f2ddb8deac03caae86679d8');
define('SHOPIFY_SECRET','2131a6e7c5e14fb6b4e90af7f0ffab95');
/**
 *  Define your APP`s key and secret(production)
 */
//define('SHOPIFY_API_KEY','ac6b9324cb3ac65fb460bde979ff98c3');
//define('SHOPIFY_SECRET','499ca299d1f0dd2be429b0ad76dd053c');

/**
 *  Define requested scope (access rights) - checkout https://docs.shopify.com/api/authentication/oauth#scopes  
 */

/*
define('TESTMODE','yes'); 
define('ACCOUNTENTITY','AMS'); 
define('ACCOUNTNUMBER','1045'); 
define('ACCOUNTPIN','553654'); 
define('SITECODE','SPLWH'); 

define('SHOPIFY_APPLICATION_KEY','10be10ef1d9eebfecd588602ea0b4c27'); 
define('SHOPIFY_APPLICATION_PASSWORD','9bcbf7ef9fe32c840d80e0a864a1ad8c'); 

define('TYMEZOME','Europe/Kiev'); 
 * */
