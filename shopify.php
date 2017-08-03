<?php
class Shopify {

  //declare properties
  private $shopify_key;
  private $shopify_password;
  private $shopify_store;

  public function __construct($key, $password, $store){

    $this->shopify_key = $key;
    $this->shopify_password = $password;
    $this->shopify_store = $store;
    $this->shopify_url = 'https://'.$this->shopify_key.':'.$this->shopify_password.'@'.$this->shopify_store.'.myshopify.com/admin/';

  }

  public function makeRequest($url_slug, $method, $query){

    $url = $this->shopify_url.$url_slug;
    $url = $this->curlAppendQuery($url, $query);
    $ch = curl_init($url);
    $this->curlSetopts($ch, $method);

    $response = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);

    if ($errno) throw new ShopifyCurlException($error, $errno);

    return $response;

  }

  public function getFeed($filename){

    $products_count = $this->makeRequest('products/count.json', 'GET', array('published_status'=>'published'));
    $limit = 250;

    $products_count = json_decode($products_count, TRUE);
    $pages = ceil($products_count["count"]/$limit);

    $shop_get = $this->makeRequest('shop.json', 'GET', '');
    $shop_info = json_decode($shop_get, TRUE);

    $file = fopen($filename,"w");

    for($i=1; $i<=$pages; $i++) {

    $file = fopen($filename,"w");
    $headers = 'Product Name|Parent ID|Variant ID|SKU|Retail Price|Sale Price|Variant Link|Variant Image|Medium Image|Small Image|Category|Description|Barcode|Option 1|Option 2|Option 3|Tags|Brand
';
    fwrite($file, $headers);

        $products = $this->makeRequest('/products.json', 'GET', array("limit" => $limit, "page" => $i, "published_status" => "published"));
        $products = json_decode($products, TRUE);

        foreach($products["products"] as $product) {
            $product_title = $product['title'];
            $product_id = $product['id'];
            $product_image = $product['image']['src'];
            $product_department = $product['product_type'];
            $product_url = 'https://'.$shop_info['shop']['domain'].'/products/'.$product['handle'];
            $product_description = strip_tags($product['body_html']);
            $product_tags = $product['tags'];
            $product_vendor = $product['vendor'];

            foreach($product['variants'] as $variant) {
                $product_variant_id = $variant['id'];
                $product_variant_price = $variant['price'];
                $product_variant_sale_price = $variant['compare_at_price'];
                $product_variant_url = $product_url . '?variant=' . $product_variant_id;
                $product_variant_sku = $variant['sku'];
                $product_variant_barcode = $variant['barcode'];
                if ($variant['option1'] === 'Default') {
                  $product_variant_option1 = '';
                }
                else {
                  $product_variant_option1 = $variant['option1'];
                }
                $product_variant_option2 = $variant['option2'];
                $product_variant_option3 = $variant['option3'];
                $product_variant_image_id = $variant['image_id'];
                $product_variant_image = $product_image;
                foreach ($product['images'] as $image) {
                  if ($image['id'] === $product_variant_image_id) {
                    $product_variant_image = $image['src'];
                  }
                }

                $variant_data = array(
                  $product_title,
                  $product_id,
                  $product_variant_id,
                  $product_variant_sku,
                  $product_variant_price,
                  $product_variant_sale_price,
                  $product_variant_url,
                  $product_variant_image,
                  $product_variant_image,
                  $product_variant_image,
                  $product_department,
                  $product_description,
                  $product_variant_barcode,
                  $product_variant_option1,
                  $product_variant_option2,
                  $product_variant_option3,
                  $product_tags,
                  $product_vendor
                );

                fputcsv($file, $variant_data, $delimiter = "|");
            }
        }
    }

    fclose($file);

  }

  private function curlSetopts($ch, $method)
	{
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'shopify-php-api-client');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $method);
	}

  private function curlAppendQuery($url, $query)
	{
		if (empty($query)) return $url;
		if (is_array($query)) return "$url?".http_build_query($query);
		else return "$url?$query";
	}

}

class ShopifyCurlException extends Exception { }
class ShopifyApiException extends Exception
{
	protected $method;
	protected $path;
	protected $params;
	protected $response_headers;
	protected $response;

	function __construct($method, $path, $params, $response_headers, $response)
	{
		$this->method = $method;
		$this->path = $path;
		$this->params = $params;
		$this->response_headers = $response_headers;
		$this->response = $response;

		parent::__construct($response_headers['http_status_message'], $response_headers['http_status_code']);
	}
	function getMethod() { return $this->method; }
	function getPath() { return $this->path; }
	function getParams() { return $this->params; }
	function getResponseHeaders() { return $this->response_headers; }
	function getResponse() { return $this->response; }
}
?>
