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

# to exectue:
#   php -f ./src/token.test.php CLIENT_ID CLIENT_SECRET

class DevEX_Token_Test {

  public function __construct($client_id, $client_secret, $password) {
    include_once 'token.php';
    print_r("Executing DevEX_Token_Test...\n");

    $token = new DevEX_Token($client_id, $client_secret, $password);
    $token->fetch_access_token();

    print_r("Decrypted Token: " . $token->get_decrypted_access_token());

    exit();
  }
}

$client_id = isset($argv[1]) ? $argv[1] : null;
$client_secret = isset($argv[2]) ? $argv[2] : null;
$password = isset($argv[3]) ? $argv[3] : null;
new DevEX_Token_Test($client_id, $client_secret, $password);
?>
