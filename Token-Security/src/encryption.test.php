<?php

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
