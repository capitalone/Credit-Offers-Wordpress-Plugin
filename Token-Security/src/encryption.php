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
 * Contains basic encryption mechanisms
 *
 * @package    DevEX_Encryption
 * @author     Capital One <devexchange-support@capitalone.com>
 */
class DevEX_Encryption {

  const BYTES_FOR_256_BIT_KEY = 32; //Bytesize for 256-bit key.

  /**
   * Default parameters for keypair config.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $_default_digest_method    keypair digest algorithm
   * @var      integer   $_default_private_key_bit_size    keypair private key size
   */
  private static $_default_digest_method = "aes-256-cbc";
  private static $_default_private_key_bit_size = 4096;

  /**
   * Return pkcs12 config generation parameters
   *
   * @since   1.0.0
   * @param   string    $alg          Optional digest algorithm specification for keypair generation config .
   * @param   integer   $bit_count    Optional private_key_bits specification for keypair generation config.
   * @param   string    $key_type     Optional private_key_type specification for keypair generation config.
   * @return  arary
   */
  private static $_config;
  public static function get_config( $alg = null, $bit_count = null, $key_type = null ) {
    if (self::$_config == null) {
      if(!$alg) $alg = self::$_default_digest_method;
      if(!$bit_count) $bit_count = self::$_default_private_key_bit_size;
      if(!$key_type) $key_type = OPENSSL_KEYTYPE_RSA;
      self::$_config = array(
        "digest_alg" => $alg,
        "private_key_bits" => $bit_count,
        "private_key_type" => $key_type,
      );
    }
    return self::$_config;
  }

  /**
   * Return IV length
   *
   * @since   1.0.0
   * @return  integer
   */
  private static function aes_iv_length() {
    return openssl_cipher_iv_length(self::$_default_digest_method); // AES IV length is 128-bit
  }

  /**
   * Generate pkcs12 and return it with public_key and password
   *
   * @since   1.0.0
   * @param   string    $password     A password to encrypt the pkcs12.
   * @param   string    $alg          Optional algorithm specification for keypair generation.
   * @param   integer   $bit_count    Optional bit_count for keypair generation.
   * @return  arary
   */
  public static function create_key_pair( $password, $alg = null, $bit_count = null ) {
    $privateKey = null;
    $config = self::get_config( $alg, $bit_count );
    $keyPair = openssl_pkey_new($config);
    openssl_pkey_export($keyPair, $privateKey);

    $res_csr = openssl_csr_new(array(), $privateKey, $config);
    $res_cert = openssl_csr_sign($res_csr, null, $privateKey, 0);

    openssl_x509_export($res_cert, $str_cert);

    openssl_pkcs12_export($str_cert, $pkcs12, $privateKey, $password);

    return array(
      'public_key' => openssl_pkey_get_details(openssl_pkey_get_public($str_cert))['key'],
      'pkcs12' => base64_encode($pkcs12),
      'password' => $password
    );
  }
  /**
   * Return private key from base64_encoded pkcs12.
   *
   * @since   1.0.0
   * @param   string    $pkcs12            A base64_encoded pkcs12.
   * @param   string    $password    Password for pkcs12.
   * @return  string
   */
  public static function return_private_key( $pkcs12, $password ) {
    openssl_pkcs12_read( base64_decode($pkcs12), $certs, $password );
    return $certs['pkey'];
  }

  /**
   * Encrypt a value.
   *
   * @since   1.0.0
   * @param   string    $msg            The message to be encrypted.
   * @param   string    $public_key     The public key used for encryption.
   * @param   string    $alg            Algorithm for encryption (optional)
   * @return  string
   */
  public static function encrypt( $msg, $public_key, $alg = null ) {
    $encryption_key = openssl_random_pseudo_bytes(self::BYTES_FOR_256_BIT_KEY);
    $iv = openssl_random_pseudo_bytes(self::aes_iv_length());

    if(!$alg) $alg = self::$_default_digest_method;
    $encrypted = openssl_encrypt($msg, $alg, $encryption_key, 0, $iv);

    openssl_public_encrypt($encryption_key, $encrypted_encryption_key, $public_key);
    $payload = array(
      "msg" => $encrypted,
      "iv" => base64_encode($iv),
      "key" => base64_encode($encrypted_encryption_key)
    );
    return json_encode($payload);
  }

  /**
   * Decrypt a value from its encrypted state.
   *
   * @since   1.0.0
   * @param   string    $msg            The encrypted message in string form.
   * @param   string    $private_key    The private key used for decryption.
   * @param   string    $alg            Algorithm for decryption (optional)
   * @return  string or null
   */
  public static function decrypt( $msg, $private_key, $alg = null ) {
    if( $encrypted = json_decode($msg, true) )
    {
      if(!$alg) $alg = self::$_default_digest_method;
      openssl_private_decrypt(base64_decode($encrypted['key']), $encryption_key, $private_key);
      if( $decrypted = openssl_decrypt($encrypted['msg'], $alg, $encryption_key, 0, base64_decode($encrypted['iv'])) ) {
        return $decrypted;
      }
    }
    return null;
  }

}

?>
