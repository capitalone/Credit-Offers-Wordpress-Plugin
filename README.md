# Due to changes in the priorities, this project is currently not being supported. The project is archived as of 9/17/21 and will be available in a read-only state. Please note, since archival, the project is not maintained or reviewed. #

# Credit Offers Wordpress Plugin

Point & click configuration of Credit Offers API for Wordpress

## Setup

- Download release and install as plugin in Wordpress.
- Set Client Id & Client Secret
- Set path for generated configuration file to live (should be outside web root)
- Set password for generated PKCS#12 keystore

## Usage

Retrieve Product Listings

```php
if ( method_exists('Wp_Creditoffers', 'get_product_listings' ) ) {
  // Get listings from API or cache
  $product_listings = Wp_Creditoffers::get_product_listings();

  // Get products
  $products = $product_listings->products;

  // Pull a product from the array
  $product = $products[0];
}
```

Retrieve Product Details

```php
// $product is a product from the listings call above
if( method_exists('Wp_Creditoffers', 'get_product_details' ) {
  // Pull product details from API or cache
  $product_details = Wp_Creditoffers::get_product_details($product->productType, $product->productId);
}
```

## Details

- Product Detail Cache is stored in `wp_co_product_details` table
- Encrypted data and listings are stored in `wp_options` table

#### Option Names

- `co_ini_path` - path of file containing pkcs12 password
- `co_client_credentials` - encrypted client credentials
- `co_pkcs12` - base64 encoded pkcs12 key
- `co_public_key` - derived public key
- `co_access_token` - encrypted access token
- `co_access_token_expiry` - access token expiry
- `co_product_listings` - cached product listings
- `co_product_listings_expiry` - product listing expiry


## Contributors: 

We welcome your interest in Capital One’s Open Source Project (the “Project”). Any Contributor to the Project must accept and sign a CLA indicating agreement to the license terms. Except for the license granted in this CLA to Capital One and to recipients of software distributed by Capital One, you reserve all right, title, and interest in and to your contributions; this CLA does not impact your rights to use your own contributions for any other purpose. 

[Link to Individual CLA](https://docs.google.com/forms/d/19LpBBjykHPox18vrZvBbZUcK6gQTj7qv1O5hCduAZFU/viewform) 

[Link to Corporate CLA](https://docs.google.com/forms/d/e/1FAIpQLSeAbobIPLCVZD_ccgtMWBDAcN68oqbAJBQyDTSAQ1AkYuCp_g/viewform)

This project adheres to the [Open Source Code of Conduct](https://developer.capitalone.com/single/code-of-conduct/). By participating, you are expected to honor this code.
