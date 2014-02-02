<?php
/****************************************************
constants.php

This is the configuration file for the samples.This file
defines the parameters needed to make an API call.
****************************************************/

//$sandbox = FALSE;
$sandbox = TRUE;

if ($sandbox == TRUE)
{
	define('API_USERNAME', 'jinjin_1354227916_biz_api1.gmail.com');
    define('API_PASSWORD', '1354227972');
    define('API_SIGNATURE', 'AiPC9BjkCyDFQXbSkoZcgqH3hpacA.8fp7Gd5zO-30qbuUVwbHVUygHk');
    define('API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp');
    define('PAYPAL_URL', 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=');
}
else
{
    define('API_USERNAME', '');
    define('API_PASSWORD', '');
    define('API_SIGNATURE', '');
    define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
    define('PAYPAL_URL', 'https://www.paypal.com/webscr&cmd=_express-checkout&token=');
}

define('USE_PROXY',FALSE);
define('PROXY_HOST', '127.0.0.1');
define('PROXY_PORT', '808');

/**
# Version: this is the API version in the request.
# It is a mandatory parameter for each API request.
# The only supported value at this time is 2.3
*/

define('VERSION', '58.0');

?>
