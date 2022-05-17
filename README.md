# skeleton

## Description

This collection of files makes up `skeleton`, a tiny "framework" which allows
you to quickly develop small or large web applications.

We don't like to call it a framework, since it is much less than that.

### Rationale

The reason why we created and maintain `skeleton` instead of using something
more mainstream, is that we want something to suggest a certain structure and a
way of working, but without getting in your way. `skeleton` will give you a lot
of freedom to do things your way.

We re-use components from other projects whenever it makes sense. However, we
will implement our own versions of simple components if that can avoid another
dependency. We don't want another [is-odd](https://www.npmjs.com/package/is-odd)
incident.

We have open-sourced `skeleton`, because we believe it may be of inspiration to
others. We have chosen a very liberal license, so you are free to use it for
any project you may have.

## Installation

  * Install the code with composer: `composer create-project tigron/skeleton`
  * Drop the code somewhere on a webserver
  * Point the document root (or equivalent) to `webroot/`
  * Make sure your server sends all requests to `webroot/handler.php`, if your
    server supports `.htaccess` files, that should happen automatically
  * Create a database
  * Create a `confog/environment.php` file, containing at least the DSN for your
    database
  * Run `composer update`
  * Run `util/bin/skeleton migrate:up`

## Usage

### Getting started

`skeleton` is well suited for use without a webserver or a web front-end if that
is not your thing. Likewise, it can easily be used without a database. Simply
install or remove the relevant packages at will. Only the `skeleton-core`
package is required.

That being set, the default `skeleton` setup comes with a few packages already
required in `composer.json`. Feel free to add or remove any package you don't
need.

To get started, have a look at the `app/admin` folder. You will find an example
module in `module/`, an example event handler in `event/` and some basic
templates in `template/`.

### Packages

The `skeleton` project is made up of loosely coupled packages. These packages
implement commonly used components, tools, scripts and anything you can think
of. The rule of thumb is: if you need it twice, create a package.

An overview can be found [here](https://github.com/tigron?q=skeleton).

### Database

As with many things in `skeleton` you are mostly free to do what you want. But
if you want things to work automagically, we expect you to follow a few simple
guidelines.

  * table names are always singular
  * every table has an `id` column, which is auto-incremented
  * references to rows in other tables are to be done with foreign keys, and
    will reference the id in the remote table as `<table>_id`.
  * the `uuid`, `created`, `updated` and `archived` columns are magic

Some `skeleton` packages will create their own tables. These tables are owned by
the package. Do not add, remove or modify columns in these tables outside of the
packages.

Some packages allow you to specify a table name, but most do not. If your
application is already using a table with the same name as a package you want to
use, you will probably need to modify your application.

### Environment

The `.environment.php` file in the root of your project is mostly an extension
of your `config/Config.php` file. The former is meant for variables which will
differ per environment, while the latter should be committed to your VCS and
will be rather static in nature.

A minimal example can be seen below.

    <?php
    $environment = [
        'database' => 'mysqli://username:password@localhost/database',
    ];

## Features

Hardly any by default. Many exist as separate packages.
