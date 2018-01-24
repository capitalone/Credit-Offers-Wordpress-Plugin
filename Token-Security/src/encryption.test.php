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
class DevEX_Encryption_Test {

  public function __construct() {
    include_once 'encryption.php';
    print_r("Executing DevEX_Encryption_Test...\n");

    $msg = strlen(func_get_arg(0)) > 0 ? func_get_arg(0) : "message to be encrypted";

    print_r("encrypting $msg...\n");

    $key_pair = DevEX_Encryption::create_key_pair('password');

    $public_key = $key_pair['public_key'];
    $private_key = DevEX_Encryption::return_private_key( $key_pair['pkcs12'], $key_pair['password'] );

    $encrypted = DevEX_Encryption::encrypt($msg, $public_key);
    $decrypted = DevEX_Encryption::decrypt($encrypted, $private_key);

    if(assert($msg == $decrypted, "decrypted message does not match original; $msg != $decrypted")) {
      print_r("DevEX_Encryption_Test passed; $msg == $decrypted\n");
    }

  }
}
new DevEX_Encryption_Test($argv[1]);
?>
