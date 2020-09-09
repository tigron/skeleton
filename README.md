# skeleton

## Description

This collection of files makes up "skeleton", a tiny "framework" which
allows you to quickly develop small or large web applications.

We don't like to call it a framework, since it is much less than that.

## Installation

  * Install the code with composer: `composer create-project tigron/skeleton`
  * Put the code somewhere on a webserver that supports `.htaccess` files
  * Point your DocumentRoot (or equivalent) to ./webroot
  * If you ignored the first instruction, make sure to somehow rewrite all requests to handler.php
  * Create a .environment.php file, containing at least the DSN for your database
  * Create a database (import database.sql which can be found in ./config)

### Environment

    <?php
    $environment = [
        'database' => 'mysqli://username:password@localhost/database',
    ];

## Features

  * Hardly any.
