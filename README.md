# ARC2

[![Latest Stable Version](https://poser.pugx.org/semsol/arc2/v/stable.svg)](https://packagist.org/packages/semsol/arc2)
[![Total Downloads](https://poser.pugx.org/semsol/arc2/downloads.svg)](https://packagist.org/packages/semsol/arc2)
[![Latest Unstable Version](https://poser.pugx.org/semsol/arc2/v/unstable.svg)](https://packagist.org/packages/semsol/arc2)
[![License](https://poser.pugx.org/semsol/arc2/license.svg)](https://packagist.org/packages/semsol/arc2)

ARC2 is a PHP 8.0+ library for working with RDF.
It also provides a MySQL-based triplestore with SPARQL support.
Older versions of PHP may work, but are not longer tested.

**Test status:**

| Database      | Status                                                                          |
|---------------|---------------------------------------------------------------------------------|
| MariaDB 10.5  | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.5%20Tests/badge.svg)  |
| MariaDB 10.6  | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.6%20Tests/badge.svg)  |
| MariaDB 10.9  | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.9%20Tests/badge.svg)  |
| MariaDB 10.10 | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.10%20Tests/badge.svg) |
| MariaDB 10.11 | ![](https://github.com/semsol/arc2/workflows/MariaDB%2010.11%20Tests/badge.svg) |
| MySQL 5.5     | ![](https://github.com/semsol/arc2/workflows/MySQL%205.5%20Tests/badge.svg)     |
| MySQL 5.6     | ![](https://github.com/semsol/arc2/workflows/MySQL%205.6%20Tests/badge.svg)     |
| MySQL 5.7     | ![](https://github.com/semsol/arc2/workflows/MySQL%205.7%20Tests/badge.svg)     |
| MySQL 8.0     | ![](https://github.com/semsol/arc2/workflows/MySQL%208.0%20Tests/badge.svg)     |
| MySQL 8.1     | ![](https://github.com/semsol/arc2/workflows/MySQL%208.1%20Tests/badge.svg)     |

## Documentation

For the documentation, see the [Wiki](https://github.com/semsol/arc2/wiki#core-documentation). To quickly get started, see the [Getting started guide](https://github.com/semsol/arc2/wiki/Getting-started-with-ARC2).

## Installation

Package available on [Composer](https://packagist.org/packages/semsol/arc2).

You should use Composer for installation:

```bash
composer require semsol/arc2:^3
```

Further information about Composer usage can be found [here](https://getcomposer.org/doc/01-basic-usage.md#autoloading), for instance about autoloading ARC2 classes.

## Requirements

**PHP:** 8.0+

#### Database systems

This section is relevant, if you wanna use database related functionality.

**MySQL**

| 5.5  | 5.6  | 5.7  | 8.0  |
|------|------|------|------|
| :+1: | :+1: | :+1: | :+1: |

**MariaDB**

| 10.1 | 10.2 | 10.3 | 10.4 | 10.5 |
|------|------|------|------|------|
| :+1: | :+1: | :+1: | :+1: | :+1: |

## RDF triple store

### SPARQL support

Please have a look into [SPARQL-support.md](doc/SPARQL-support.md) to see which SPARQL 1.0/1.1 features are currently supported.

## Internal information for developers

Please have a look [here](doc/developer.md) to find information about maintaining and extending ARC2 as well as our docker setup for local development.
