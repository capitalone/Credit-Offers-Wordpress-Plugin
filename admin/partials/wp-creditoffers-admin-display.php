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
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://developer.capitalone.com
 * @since      1.0.0
 *
 * @package    Wp_Creditoffers
 * @subpackage Wp_Creditoffers/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form method="post" name="credit_offers_options" action="options.php">
      <?php
        settings_fields($this->plugin_name);
        if (!get_option('co_access_token')) {
      ?>
      <!-- remove some meta and generators from the <head> -->
      <fieldset>
          <legend class="screen-reader-text"><span>Client ID</span></legend>
          <label for="<?php echo $this->plugin_name; ?>-client-id">
            <span><?php esc_attr_e('Client ID', $this->plugin_name); ?></span>
            <input type="text" id="<?php echo $this->plugin_name; ?>-client-id" name="<?php echo $this->plugin_name; ?>[client_id]" value=""/>
          </label>
      </fieldset>

      <fieldset>
          <legend class="screen-reader-text"><span>Client Secret</span></legend>
          <label for="<?php echo $this->plugin_name; ?>-client-secret">
            <span><?php esc_attr_e('Client Secret', $this->plugin_name); ?></span>
            <input type="text" id="<?php echo $this->plugin_name; ?>-client-secret" name="<?php echo $this->plugin_name; ?>[client_secret]" value=""/>
          </label>
      </fieldset>

      <fieldset>
          <legend class="screen-reader-text"><span>INI Path</span></legend>
          <label for="<?php echo $this->plugin_name; ?>-ini-path">
            <span><?php esc_attr_e('INI Path', $this->plugin_name); ?></span>
            <input type="text" id="<?php echo $this->plugin_name; ?>-ini-path" name="<?php echo $this->plugin_name; ?>[ini_path]" value="" />
          </label>
      </fieldset>
      <fieldset>
          <legend class="screen-reader-text"><span>Private Key Password</span></legend>
          <label for="<?php echo $this->plugin_name; ?>-key-pw">
            <span><?php esc_attr_e('Private Key Password', $this->plugin_name); ?></span>
            <input type="text" id="<?php echo $this->plugin_name; ?>-key-pw" name="<?php echo $this->plugin_name; ?>[key_pw]" value=""/>
          </label>
      </fieldset>
      <fieldset>
        <input type="radio" id="envSandbox" name="<?php echo $this->plugin_name; ?>[environment]" value="sandbox" checked="checked">
        <label for="envSandbox">Sandbox</label>
        <input type="radio" id="envProduction" name="<?php echo $this->plugin_name; ?>[environment]" value="production">
        <label for="envProduction">Production</label>
      </fieldset>
      <?php
        submit_button( 'Save all changes', 'primary', 'submit', true );
        }
        else {
          submit_button('Remove Client Credentials', 'delete');
        }
        $product_listings = Wp_Creditoffers::get_product_listings();
        if($product_listings && $product_listings->products) {
            echo "<div class=\"product_listings\">";
            $products = $product_listings->products;
            for($i = 0; $i < count($products); $i++) {
              $product = $products[$i];
              echo "<div class=\"row\">".
                "<h4>".$product->productDisplayName." (".$product->productType.")</h4>".
                "<pre>";
              $product_details = self::get_product_details($product->productType, $product->productId);
              print_r($product_details);
              echo "</pre>".
              "</div>";
              //var_dump($product);
            }
            echo "</div>";
        } else {
          var_dump($product_listings);
        }
      ?>
    </form>

</div>
