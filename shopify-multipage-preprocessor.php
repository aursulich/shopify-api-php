<?php
require 'shopify.php';
define('SHOPIFY_KEY', '42602e8c91e2cfa1192c1dc4b9290316');
define('SHOPIFY_PASSWORD', '948d1b4efca537436ea24d868acbb47e');
define('SHOPIFY_STORE', 'avantlink');

$shopify = new Shopify(SHOPIFY_KEY, SHOPIFY_PASSWORD, SHOPIFY_STORE);

//$shopify->makeRequest('products.json', 'GET', array('limit'=>'250'));

$products_count = $shopify->makeRequest('products/count.json', 'GET', array('published_status'=>'published'));
$limit = 250;

$products_count = json_decode($products_count, TRUE);
$pages = ceil($products_count["count"]/$limit);

$file = fopen("feed.csv","w");

for($i=1; $i<=$pages; $i++) {

    $products = $shopify->makeRequest('/products.json', 'GET', array("limit" => $limit, "page" => $i, "published_status" => "published"));
    $products = json_decode($products, TRUE);

    foreach($products["products"] as $product) {
        $product_title = $product['title'];
        echo $product_title;
        foreach($product['variants'] as $variant) {
            $product_variant_id = $variant['id'];
            $product_variant_price = $variant['price'];

            $variant_data = array($product_title, $product_variant_id, $product_variant_price);
            fputcsv($file, $variant_data);
        }
    }
}

fclose($file);
?>
