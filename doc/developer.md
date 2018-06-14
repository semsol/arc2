# Developer information

This document contains information about ARC2 internals which are relevant for maintaining and extending ARC2.

## Run test environment

To run test environment execute:

```bash
make test
```

Tests are split into different groups, currently:
* unit
* db_adapter_depended

You can run the `unit` group directly, but you need to set some environment variables for `db_adapter_depended`.
For more information please have a look into our `Makefile`.

#### config.php

Currently, we use the following standard db credentials to connect with the database:

```php
$dbConfig = array(
    'db_name' => 'testdb',
    'db_user' => 'root',
    'db_pwd'  => '',
    'db_host' => '127.0.0.1',
);
```

The is used in the travis environment. If you have different credentials, copy the `tests/config.php.dist` to `tests/config.php` and set your credentials.

## Editor

Please make sure your editor uses our `.editorconfig` file.
