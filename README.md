# ARC2

[![Build](https://travis-ci.org/semsol/arc2.svg?branch=master)](https://travis-ci.org/semsol/arc2)
[![Coverage Status](https://coveralls.io/repos/github/semsol/arc2/badge.svg?branch=master)](https://coveralls.io/github/semsol/arc2?branch=master)
[![Latest Stable Version](https://poser.pugx.org/semsol/arc2/v/stable.svg)](https://packagist.org/packages/semsol/arc2)
[![Total Downloads](https://poser.pugx.org/semsol/arc2/downloads.svg)](https://packagist.org/packages/semsol/arc2)
[![Latest Unstable Version](https://poser.pugx.org/semsol/arc2/v/unstable.svg)](https://packagist.org/packages/semsol/arc2)
[![License](https://poser.pugx.org/semsol/arc2/license.svg)](https://packagist.org/packages/semsol/arc2)

ARC2 is a PHP 5.6+ library for working with RDF. It also provides a MySQL-based triplestore with SPARQL support.

## Installation

Package available on [Composer](https://packagist.org/packages/semsol/arc2).

If you're using Composer to manage dependencies, you can use

```bash
composer require semsol/arc2:2.4.*
```
## Requirements

#### PHP

|          5.6          |        7.0         |        7.1         |        7.2         |
|:---------------------:|:------------------:|:------------------:|:------------------:|
| :heavy_check_mark:(1) | :heavy_check_mark: | :heavy_check_mark: | :heavy_check_mark: |

(1) It is compatible with PHP 5.3+ but old versions are no longer tested.

#### Database systems

|           |        5.5         |        5.6         |        5.7         |       8.0       |
|:---------:|:------------------:|:------------------:|:------------------:|:---------------:|
| **MySQL** | :heavy_check_mark: | :heavy_check_mark: | :heavy_check_mark: | :collision: (1) |

|             |        10.0        |        10.1        |        10.2        |        10.3        |
|:-----------:|:------------------:|:------------------:|:------------------:|:------------------:|
| **MariaDB** | :heavy_check_mark: | :heavy_check_mark: | :heavy_check_mark: | :heavy_check_mark: |

(1) As long as ARC2 uses mysqli, a connection to MySQL Server 8.0 is not possible. For more information, please look [here](https://github.com/semsol/arc2/commit/0ad48d61753b15ae02ff19f615b14aa52b6557f1). But its planned to switch to PDO ([issue](https://github.com/semsol/arc2/issues/109))


## RDF triple store

Please have a look into [SPARQL-support.md](doc/SPARQL-support.md) to see which SPARQL 1.0/1.1 features are currently supported.

## Internal information for developers

Please have a look [here](doc/developer.md) to find information about maintaining and extending ARC2.
