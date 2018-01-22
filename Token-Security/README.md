# Token-Security-Example-PHP

## Overview

PHP implementation to retrieve and secure an access token from the DevExchange API. Sandbox and production environment supported.

## Usage

### Basic implementation
```php
  # Pass the client credentials to DevEX_Token class
  $token = new DevEX_Token($client_id, $client_secret);

  # Get encrypted access token
  print_r($token->get_access_token()); # returns in json_encoded format { "msg": "ENCRYPTED", "key": "ENCRYPTED", "iv": "..." }

  # Get decrypted access token
  print_r($token->get_decrypted_access_token()); # prints as string

```
