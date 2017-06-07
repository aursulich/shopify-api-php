<?php
require 'shopify.php';
define('SHOPIFY_KEY', '42602e8c91e2cfa1192c1dc4b9290316');
define('SHOPIFY_PASSWORD', '948d1b4efca537436ea24d868acbb47e');
define('SHOPIFY_STORE', 'avantlink');

$shopify = new Shopify(SHOPIFY_KEY, SHOPIFY_PASSWORD, SHOPIFY_STORE);

//$shopify->makeRequest('products.json', 'GET', array('limit'=>'250'));

$shopify->getFeed("feed2.csv");

?>
