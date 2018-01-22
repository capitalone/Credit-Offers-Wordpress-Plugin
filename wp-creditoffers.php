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
 * @link              https://developer.capitalone.com
 * @since             1.0.0
 * @package           Wp_Creditoffers
 *
 * @wordpress-plugin
 * Plugin Name:       CreditOffers
 * Plugin URI:        https://developer.capitalone.com
 * Description:       Plugin for consuming CreditOffers API
 * Version:           1.0.0
 * Author:            Capital One
 * Author URI:        https://developer.capitalone.com
 * Text Domain:       wp-creditoffers
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
require_once plugin_dir_path( __FILE__ ) . 'admin/class-wp-creditoffers-admin.php';

class WP_Creditoffers {

	private static $plugin_name = 'wp-creditoffers';
	private static $version = '1.0.0';


	protected $admin = null;



 	public function __construct() {
		$this->admin = new WP_Creditoffers_Admin(self::$plugin_name, self::$version, plugin_dir_path( __FILE__ ));
		$this->admin->run(plugin_basename( __FILE__ ));
	}

	public static function activate() {
		global $wpdb;

		$table_name = WP_Creditoffers_Admin::get_table_names()['product_details'];

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			product_id tinytext NOT NULL,
			details text NOT NULL,
			expires int NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function deactivate() {
		global $wpdb;
		$table_name = WP_Creditoffers_Admin::get_table_names()['product_details'];
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

		WP_Creditoffers_Admin::clear_credentials();
		Wp_Creditoffers_Admin::clear_cache();
	}

	public static function get_product_listings() {
		return WP_Creditoffers_Admin::get_product_listings();
	}
}


/**
 * @since    1.0.0
 */
function run_wp_creditoffers() {
	$plugin = new WP_Creditoffers();

}
register_activation_hook( __FILE__, array( 'WP_Creditoffers', 'activate'));
register_deactivation_hook( __FILE__, array( 'WP_Creditoffers', 'deactivate'));
run_wp_creditoffers();
