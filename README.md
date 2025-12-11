# Phpolar Migrations

Adds support for running database migrations in your application

[![Coverage Status](https://coveralls.io/repos/github/phpolar/migrations/badge.svg)](https://coveralls.io/github/phpolar/migrations)
[![Latest Stable Version](http://poser.pugx.org/phpolar/migrations/v)](https://packagist.org/packages/phpolar/migrations) [![Total Downloads](http://poser.pugx.org/phpolar/migrations/downloads)](https://packagist.org/packages/phpolar/migrations) [![License](http://poser.pugx.org/phpolar/migrations/license)](https://packagist.org/packages/phpolar/migrations) [![PHP Version Require](http://poser.pugx.org/phpolar/migrations/require/php)](https://packagist.org/packages/phpolar/migrations)

## Objectives

A database agnostic library that provides support for handling creation, running, and reverting migrations.

## Requirements

* A PDO connection.
* The queries and statements compatible with the database variant you choose.

## API Documentation

See <https://phpolar.github.io/migrations/>

## Thresholds

**Source Code Size:** *15kB

|     Command     |Memory Usage|
|-----------------|------------|
|     **create**  | 100 kB     |
|     **run**     | 22 kB      |
|     **revert**  | 20 kB      |

* Note: Does not include comments.

## References

* <https://www.php.net/manual/en/book.pdo.php>
* <https://www.php.net/manual/en/pdo.drivers.php>
