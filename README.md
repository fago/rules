Rules for Drupal 8
------------------

[![Build Status](https://travis-ci.org/fago/rules.svg?branch=8.x-3.x)](https://travis-ci.org/fago/rules)

The Rules module allows site administrators to define conditionally executed
actions based on occurring events (ECA-rules).

Project homepage: http://drupal.org/project/rules

Contributing
------------

Development should be done on Github by sending pull requests. Every pull
request should have an issue in the [drupal.org Rules issue queue](http://drupal.org/project/issues/rules).

Executing the automated tests
-----------------------------

This module comes with PHPUnit tests and Simpletest integration tests. You need
a working Drupal 8 installation and a checkout of the Rules module in the
modules folder.

PHPUnit:

    $ cd /path/to/drupal-8/core
    $ ./vendor/bin/phpunit ../modules/rules

Simpletest using drush:

    $ drush test-run 'Rules, Rules conditions'

You can also execute the test cases from the web interface at
/admin/config/development/testing
