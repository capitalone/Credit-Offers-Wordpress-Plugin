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
include_once 'encryption.php';
class DevEX_Token extends DevEX_Encryption {

  /**
   * parameters for access token fetch
   *
   * @since    1.0.0
   * @access   private
   * @var      string   $_key_pair                        keypair for encrypting / decrypting
   * @var      string   $_base_url                        base url for sandbox or production
   * @var      string   $_encrypted_access_token          holds encrypted access token after fetch
   * @var      string   $_encrypted_client_credentials    holds encrypted client credentials
   */
  private $_key_pair;
  private $_base_url;
  private $_encrypted_access_token;
  private $_encrypted_client_credentials;


  private static $_environments = array(
    'sandbox' => 'https://api-sandbox.capitalone.com',
    'production' => 'https://api.capitalone.com'
  );

  /**
   * parameters for access token fetch
   *
   * @since    1.0.0
   * @var      string   $client_id
   * @var      string   $client_secret
   * @var      string   $password       password for key_pair generation
   * @var      string   $key_pair       key pair holding private, public, and password keys (if pre-generated)
   * @var      string   $environment    sandbox or production (default sandbox)
   */
  public function __construct( $client_id, $client_secret, $password = null, $key_pair = null, $environment = 'sandbox' ) {
    if( assert( $client_id && $client_secret, "Missing client credentials\n" ) && assert( strlen($password), "Missing password\n" )) {
      $this->_base_url = self::$_environments[$environment];
      $this->_key_pair = $key_pair ? $key_pair : self::create_key_pair( $password );
      $this->_encrypted_client_credentials = self::encrypt($client_id . "|" . $client_secret, $this->get_public_key());
    }
  }

  /**
   * Get decrypted access token for instance
   *
   * @since   1.0.0
   * @return  string
   */
  public function get_decrypted_access_token() {
    return self::decrypt($this->_access_token, $this->get_private_key());
  }

  /**
   * Get decrypted client credentials for instance
   *
   * @since   1.0.0
   * @return  string
   */
  private function get_decrypted_client_credentials() {
    $client_credentials = self::decrypt($this->_encrypted_client_credentials, $this->get_private_key());
    return split('[|.-]', $client_credentials);
  }

  /**
   * Get public key for instance
   *
   * @since   1.0.0
   * @return  string
   */
  private function get_public_key() {
    return $this->_key_pair['public_key'];
  }

  /**
   * Get private key for instance
   *
   * @since   1.0.0
   * @return  string
   */
  private function get_private_key() {
    $key_pair = $this->_key_pair;
    return self::return_private_key( $key_pair['pkcs12'], $key_pair['password']);
  }

  /**
   * Get access token from remote
   *
   * @since   1.0.0
   */
  public function fetch_access_token() {
    $client_credentials = $this->get_decrypted_client_credentials();
    $data = array('client_id' => $client_credentials[0], 'client_secret' => $client_credentials[1], 'grant_type' => 'client_credentials');
    $curl_opts = array(
      CURLOPT_POST => count($data),
      CURLOPT_POSTFIELDS => http_build_query($data),
      CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Content-Type: application/x-www-form-urlencoded'
      ),
      CURLOPT_RETURNTRANSFER => true
    );
    $ch = curl_init($this->_base_url . '/oauth2/token');
    curl_setopt_array($ch, $curl_opts);
    if( ! $response = curl_exec($ch))
    {
      trigger_error(curl_error($ch));
    }
    // print_r("Curl: ");
    // print_r(curl_getinfo($ch));
    // print_r("\n");
    curl_close($ch);

    $response_body = json_decode($response, true);
    if( isset($response_body['access_token']) )
    {
      $token = $response_body['access_token'];
      $this->_access_token = self::encrypt($token, $this->get_public_key());
      return $token;
    }
    else if ( isset($response_body['error_description']) )
    {
      print_r($response_body["error_description"]);
    }
    else {
      print_r("Something when wrong.");
    }
    print_r("\n");
    return null;
  }
}

?>
