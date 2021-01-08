# ARC2

[![Latest Stable Version](https://poser.pugx.org/semsol/arc2/v/stable.svg)](https://packagist.org/packages/semsol/arc2)
[![Total Downloads](https://poser.pugx.org/semsol/arc2/downloads.svg)](https://packagist.org/packages/semsol/arc2)
[![Latest Unstable Version](https://poser.pugx.org/semsol/arc2/v/unstable.svg)](https://packagist.org/packages/semsol/arc2)
[![License](https://poser.pugx.org/semsol/arc2/license.svg)](https://packagist.org/packages/semsol/arc2)

ARC2 is a PHP 7.2+ library for working with RDF. It also provides a MySQL-based triplestore with SPARQL support.
Older versions of PHP may work, but are not longer tested.

**Test status:**

| Database     | Status                                                                         |
|--------------|--------------------------------------------------------------------------------|
| MariaDB 10.1 | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.1%20Tests/badge.svg) |
| MariaDB 10.2 | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.2%20Tests/badge.svg) |
| MariaDB 10.3 | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.3%20Tests/badge.svg) |
| MariaDB 10.4 | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.4%20Tests/badge.svg) |
| MariaDB 10.5 | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.5%20Tests/badge.svg) |
| MySQL 5.5    | ![](https://github.com/semsol/arc2/workflows/MySQL%205.5%20Tests/badge.svg)    |
| MySQL 5.6    | ![](https://github.com/semsol/arc2/workflows/MySQL%205.6%20Tests/badge.svg)    |
| MySQL 5.7    | ![](https://github.com/semsol/arc2/workflows/MySQL%205.7%20Tests/badge.svg)    |

## Documentation

For the documentation, see the [Wiki](https://github.com/semsol/arc2/wiki#core-documentation). To quickly get started, see the [Getting started guide](https://github.com/semsol/arc2/wiki/Getting-started-with-ARC2).

## Installation

Package available on [Composer](https://packagist.org/packages/semsol/arc2).

You should use Composer for installation:

```bash
composer require semsol/arc2:^2
```

Further information about Composer usage can be found [here](https://getcomposer.org/doc/01-basic-usage.md#autoloading), for instance about autoloading ARC2 classes.

## Requirements

#### PHP

| 7.2  | 7.3  | 7.4  | 8.0  |
|------|------|------|------|
| :+1: | :+1: | :+1: | :+1: |

#### Database systems

This section is relevant, if you wanna use database related functionality.

**MySQL**

| 5.5  | 5.6  | 5.7  | 8.0             |
|------|------|------|-----------------|
| :+1: | :+1: | :+1: | :collision: (1) |

**MariaDB**

| 10.0          | 10.1 | 10.2 | 10.3 | 10.4 | 10.5 |
|---------------|------|------|------|------|------|
| :question:(2) | :+1: | :+1: | :+1: | :+1: | :+1: |

(1) As long as ARC2 uses mysqli, a connection to MySQL Server 8.0 is not possible. For more information, please look [here](https://github.com/semsol/arc2/commit/0ad48d61753b15ae02ff19f615b14aa52b6557f1).

(2) Not tested anymore, because outdated version.

## RDF triple store

### SPARQL support

Please have a look into [SPARQL-support.md](doc/SPARQL-support.md) to see which SPARQL 1.0/1.1 features are currently supported.

### In-Memory store (SQLite-based)

Using MySQL as database backend is the default way.
But you don't need MySQL, SQLite is enough to get a non-persistent in-memory store.
Use the following example code to get a ready-to-use store instance.

```php
// get a working ARC2_Store instance (with SQLite adapter)
$store = ARC2::getStore(['db_adapter' => 'pdo', 'db_pdo_protocol' => 'sqlite']);

// add 1 triple to the store
$store->query('INSERT INTO <http://example.com/> { <http://s> <http://p1> "baz" . }');

// send a SPARQL query to the store
$result = $store->query('SELECT * FROM <http://example.com/> WHERE {<http://s> <http://p1> ?o.}');

// you should get an array of array's with one entry in $result['result']['row'].
var_dump($result);

// remember, after your script finished, all data in $store is gone
```

### Use cache

The RDF store implementation provides a hash-based query cache. It works on two levels: SQL and SPARQL, which means, that it checks given SPARQL queries as well as internally generated SQL queries.

To use it, just add the following to the database configuration:

```php
$store = ARC2::getStore(array(
    'db_name' => 'testdb',
    'db_user' => 'root',
    'db_pwd'  => '',
    'db_host' => '127.0.0.1',
    // ...
    'cache_enabled' => true // <== activates cache
));
```

Per default it uses a file based cache, which stores items in the default temp folder of the system (in Linux its usually `/tmp`). But you can use another cache solution, such as memcached.

#### PSR-16 compatibility

Our cache solution is [PSR-16](https://www.php-fig.org/psr/psr-16/) compatible, which means, that you can use your own cache instance. To do that, add the following to the database configuration:

```php
$store = ARC2::getStore(array(
    'db_name' => 'testdb',
    'db_user' => 'root',
    'db_pwd'  => '',
    'db_host' => '127.0.0.1',
    // ...
    'cache_enabled' => true
    'cache_instance' => new ArrayCache() // <=== example Cache instance, managed by yourself
));
```

ARC2 uses [Symfony Cache](https://symfony.com/doc/4.1/components/cache.html) , which provides many connectors out of the box ([Overview](https://github.com/symfony/cache/tree/master/Simple)).

### Known problems/restrictions with database systems

In this section you find known problems with MariaDB or MySQL, regarding certain features. E.g. MySQL 5.5 doesn't allow FULLTEXT indexes in InnoDB. We try to encapsulate any differences in the DB adapters, so that you don't have to care about them. In case you run into problems, this section might be of help.

#### MySQL 8.0 and mysqli

Using mysqli with MySQL 8.0 as backend throws the following exception:

> mysqli_connect(): The server requested authentication method unknown to the client [caching_sha2_password]

Based on this [source](https://mysqlserverteam.com/upgrading-to-mysql-8-0-default-authentication-plugin-considerations/), one has to change the my.cnf, adding the following entry:

> [mysqld]
> default-authentication-plugin=mysql_native_password

## Internal information for developers

Please have a look [here](doc/developer.md) to find information about maintaining and extending ARC2 as well as our docker setup for local development.
