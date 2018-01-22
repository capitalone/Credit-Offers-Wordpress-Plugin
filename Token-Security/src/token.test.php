<?php

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
