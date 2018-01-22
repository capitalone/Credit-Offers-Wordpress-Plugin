<?php
/**
 * SPDX-Copyright: Copyright 2017 Capital One Services, LLC 
 * SPDX-License-Identifier: Apache-2.0 
 * Copyright 2017 Capital One Services, LLC

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at 

 * http://www.apache.org/licenses/LICENSE-2.0 

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 */
 
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://developer.capitalone.com
 * @since      1.0.0
 *
 * @package    Wp_Creditoffers
 * @subpackage Wp_Creditoffers/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Wp_Creditoffers
 * @subpackage Wp_Creditoffers/admin
 * @author     Capital One <devexchange-support@capitalone.com>
 */
class WP_Creditoffers_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    private $devex_token;

    private static $base_url = null;

    private static $product_url_mappings = null;

    private static $table_names = null;
  	public static function get_table_names() {
  		if (self::$table_names == null) {
  			self::$table_names = array(
  				"product_details" => "credit_offers_product_details"
  			);
  		}
  		return self::$table_names;
  	}


    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version, $filepath)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->wp_co_options = get_option($this->plugin_name);
        include_once $filepath . 'Token-Security/src/token.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-creditoffers-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
      wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-creditoffers-admin.js', array( 'jquery', 'media-upload' ), $this->version, false);
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */

    public function custom_plugin_admin_menu()
    {
        add_options_page(
            'Credit Offers Setup',
            'Credit Offers',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page')
        );
    }

    /**
    * Add settings action link to the plugins page.
    *
    * @since    1.0.0
    */

    public function add_action_links($links)
    {
       return array_merge(array(
         '<a href="' . admin_url('options-general.php?page=' . $this->plugin_name) . '">' . __('Settings', $this->plugin_name) . '</a>',
       ), $links);
    }
    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */

    public function display_plugin_setup_page()
    {
        include_once('partials/wp-creditoffers-admin-display.php');
    }

    public function options_update($caller)
    {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
        if(strlen(get_option('co_client_credentials'))) {
          $this->get_access_token();
        }
    }

    public function validate($input)
    {
        $ini_path = isset($input['ini_path']) && strlen(trim($input['ini_path'])) > 0 ? $input['ini_path'] : add_settings_error($this->plugin_name.'_invalid_ini_path', esc_attr('invalid_ini_path'), 'You have entered an invalid path for ini settings');

        $client_id = isset($input['client_id']) && strlen(trim($input['client_id'])) > 0 ? $input['client_id'] : add_settings_error($this->plugin_name.'_invalid_client_id', esc_attr('invalid_client_id'), 'You have entered an invalid client id');

        $client_secret = isset($input['client_secret']) && strlen(trim($input['client_secret'])) > 0 ? $input['client_secret'] : add_settings_error($this->plugin_name.'_invalid_client_secret', esc_attr('invalid_ini_path'), 'You have entered an invalid client secret');

        $key_pw = isset($input['key_pw']) && strlen(trim($input['key_pw'])) > 0 ? $input['key_pw'] : add_settings_error($this->plugin_name.'_invalid_ini_password', esc_attr('invalid_ini_password'), 'You have entered an invalid password');

        $enviroment = $input['environment'];
        if ($ini_path && $key_pw && $client_id && $client_secret)
        {
          update_option('co_ini_path', $ini_path);
          if(self::set_password($ini_path, $key_pw)) {
            $keyPair = DevEX_Token::create_key_pair($key_pw);
            update_option('co_client_credentials', DevEX_Token::encrypt($client_id . '|' . $client_secret, $keyPair['public_key']));
            update_option('co_pkcs12', $keyPair['pkcs12']);
            update_option('co_public_key', $keyPair['public_key']);
            update_option('co_environment', $enviroment);
          } else {
            add_settings_error($this->plugin_name.'_invalid_ini_path', esc_attr('invalid_ini_path'), 'You have entered an invalid filepath');
          }

        } else {
          $this->clear_all();
        }
    }

    public function run($plugin_file) {
      add_action( 'admin_enqueue_scripts', array($this, 'enqueue_styles') );
  		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
  		add_action( 'admin_init', array($this, 'options_update') );
  		add_action( 'admin_menu', array($this, 'custom_plugin_admin_menu') );

  		add_filter( 'plugin_action_links_' . $plugin_file, array($this, 'add_action_links') );
    }

    private function get_option($k)
    {
        return isset($this->wp_co_options[$k]) ? $this->wp_co_options[$k] : null;
    }
    private static function get_private_key() {
      openssl_pkcs12_read( base64_decode(get_option('co_pkcs12')), $certs, get_pw(get_option('co_ini_path')) );
      return $certs['pkey'];
    }
    public function get_access_token($client_id="", $client_secret="") {
      if(!$client_id || !$client_secret) {
        $client_credentials = self::get_client_credentials();
        $client_credentials = explode("|", $client_credentials);
        $client_id = $client_credentials[0];
        $client_secret = $client_credentials[1];
      }
      $expiry = get_option('co_access_token_expiry');
      $access_token = get_option('co_access_token');
      if ((!$expiry || (int)$expiry < microtime(true)) && $client_id && $client_secret) {
          $base_url = self::get_base_url(get_option('co_environment'));
          $auth_response = wp_remote_post($base_url . '/oauth2/token', array(
            'body' => array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'client_credentials'
            ),
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            )
          ));
          if (wp_remote_retrieve_response_code($auth_response) != 200) {
              add_settings_error($this->plugin_name.'_invalid_credentials', esc_attr('invalid_credentials'), 'Something went wrong; please try again.');
              self::clear_all();
          } else {
              $auth_response_body = json_decode(wp_remote_retrieve_body($auth_response));
              $expiry = $auth_response_body->issued_at + $auth_response_body->expires_in;
              $access_token = DevEX_Token::encrypt($auth_response_body->access_token, get_option('co_public_key'));
              update_option('co_access_token_expiry', $expiry);
              update_option('co_access_token', $access_token);
              wp_schedule_single_event($expiry, 'get_access_token_hook');
          }
      }
    }

    public static function get_product_listings()
    {
        $base_url = self::get_base_url(get_option('co_environment'));
        $product_listings_expires = get_option('co_product_listings_expiry');
        $access_token = self::get_decrypted_option('co_access_token');
        if ((int)$product_listings_expires < microtime(true) && $access_token) {
            $get_product_listings = wp_remote_get($base_url . "/credit-offers/products", array(
              'headers' => array(
                'Accept' => 'application/json; v=3',
                'Authorization' => 'Bearer ' . $access_token
              )
            ));

            $product_listings = wp_remote_retrieve_body($get_product_listings);
            if (wp_remote_retrieve_response_code($get_product_listings) == 200) {
                $product_listings_expires = wp_remote_retrieve_header($get_product_listings, 'expires');
                update_option('co_product_listings_expiry', (string)strtotime($product_listings_expires));
                update_option('co_product_listings', $product_listings);
            }
        } else {
            $product_listings = get_option('co_product_listings');
        }
        return json_decode($product_listings);
    }

    public static function get_product($id)
    {
        $base_url = self::get_base_url(get_option('co_environment'));
        $product_listings_expires = get_option('co_product_listings_expiry');
        $access_token = self::get_decrypted_option('co_access_token');
        if ((int)$product_listings_expires < microtime(true) && $access_token) {
            $get_product_listings = wp_remote_get($base_url . "/credit-offers/products", array(
              'headers' => array(
                'Accept' => 'application/json; v=3',
                'Authorization' => 'Bearer ' . $access_token
              )
            ));

            $product_listings = wp_remote_retrieve_body($get_product_listings);
            if (wp_remote_retrieve_response_code($get_product_listings) == 200) {
                $product_listings_expires = wp_remote_retrieve_header($get_product_listings, 'expires');
                update_option('co_product_listings_expiry', (string)strtotime($product_listings_expires));
                update_option('co_product_listings', $product_listings);
            }
        } else {
            $product_listings = get_option('co_product_listings');
        }
        return json_decode($product_listings);
    }

    private static function get_base_url($environment) {
      if(self::$base_url == null) {
        self::$base_url = array(
            'sandbox' => 'https://api-sandbox.capitalone.com',
            'production' => 'https://api.capitalone.com'
        );
      }
      return isset(self::$base_url[$environment]) ? self::$base_url[$environment] : null;
    }

    private static function get_product_url_type($product_type) {
      if(self::$product_url_mappings == null) {
        self::$product_url_mappings = array(
          'ConsumerCard' => 'consumer',
          'BusinessCard' => 'business'
        );
      }
      return isset(self::$product_url_mappings[$product_type]) ? self::$product_url_mappings[$product_type] : null;
    }

    public static function clear_credentials()
    {
      delete_option('co_ini_path');
      delete_option('co_environment');
      delete_option('co_pkcs12');
      delete_option('co_public_key');
      delete_option('co_client_credentials');
    }
    public static function clear_cache()
    {
        delete_option('co_product_listings_expiry');
        delete_option('co_product_listings');
        delete_option('co_access_token_expiry');
        delete_option('co_access_token');
    }

    public static function clear_all()
    {
        self::clear_cache();
        self::clear_credentials();

        global $wpdb;
    		$table_name = self::get_table_names()['product_details'];
    		$wpdb->query( "TRUNCATE $table_name" );

    }

    private static function product_details_insert($product_id, $details, $expires) {
      global $wpdb;
      $wpdb->insert(
    		self::get_product_details_table_name(),
    		array(
    			'product_id' => $product_id,
    			'details' => $details,
          'expires' => $expires
    		)
    	);
    }

    private static function product_details_update($product_id, $details, $expires) {
      global $wpdb;
      $wpdb->update(
    		self::get_product_details_table_name(),
    		array(
    			'details' => $details,
          'expires' => $expires
    		),
        array( 'product_id' => $product_id)
    	);
    }

    public static function get_product_details($product_type, $product_id) {
      global $wpdb;
      $table_name = self::get_product_details_table_name();
      $product = $wpdb->get_row( $wpdb->prepare( "SELECT product_id, details, expires FROM $table_name WHERE product_id = %s", $product_id), ARRAY_A );
      if($product['details'] && $product['expires'] > microtime(true)) $product_details = $product['details'];
      else {
        $base_url = self::get_base_url(get_option('co_environment'));
        $product_type = self::get_product_url_type($product_type);
        $access_token = self::get_decrypted_option('co_access_token');
        $get_product_details = wp_remote_get( $base_url . "/credit-offers/products/cards/" . $product_type . "/" . $product_id, array(
          'headers' => array(
            'Accept' => 'application/json; v=3',
            'Authorization' => 'Bearer ' . $access_token
          )
        ));
        $product_details = wp_remote_retrieve_body($get_product_details);
        $product_details_expire = wp_remote_retrieve_header($get_product_details, 'expires');
        if(!$product['details']) self::product_details_insert($product_id, $product_details, strtotime($product_details_expire));
        else self::product_details_update($product_id, $product_details, strtotime($product_details_expire));
      }
      return json_decode($product_details);
    }

    public static function get_product_details_table_name() {
      return self::get_table_names()['product_details'];
    }

    private static function set_password($path, $pw) {
      return @file_put_contents($path, "pkcs12_pw = \"$pw\"");
    }

    private static function get_password($path) {
      $file = @parse_ini_file($path);
      if(isset($file['pkcs12_pw'])) return $file['pkcs12_pw'];
    }

    public static function get_client_credentials() {
      $password = self::get_password( get_option('co_ini_path') );
      $private_key = DevEX_Token::return_private_key( get_option('co_pkcs12'), $password );
      return DevEX_Token::decrypt( get_option('co_client_credentials'), $private_key );
    }

    private static function get_decrypted_option($option) {
      $password = self::get_password( get_option('co_ini_path') );
      $private_key = DevEX_Token::return_private_key( get_option('co_pkcs12'), $password );
      return DevEX_Token::decrypt( get_option($option), $private_key );
    }
}
