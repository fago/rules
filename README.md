# Rules for Drupal 8

[![Build Status](https://travis-ci.org/fago/rules.svg?branch=8.x-3.x)](https://travis-ci.org/fago/rules)

The Rules module allows site administrators to define conditionally executed
actions based on occurring events (ECA-rules).

* Project homepage: http://drupal.org/project/rules
* #d8rules initiative: http://d8rules.org/
* Documentation: http://thefubhy.gitbooks.io/rules

## Contributing

For some time, development will happen on GitHub using the pull request model:
in case you are not familiar with that, please take a few minutes to read the
[GitHub article](https://help.github.com/articles/using-pull-requests) on using
pull requests.

There are a few conventions that should be followed when contributing:

* Always create an issue in the [drupal.org Rules issue queue](http://drupal.org/project/issues/rules)
  for every pull request you are working on.
* Always cross-reference the Issue in the Pull Request and the Pull Request in
  the issue.
* Always create a new branch for every pull request: its name should contain a
  brief summary of the ticket and its issue id, e.g **readme-2276369**.
* Try to keep the history of your pull request as clean as possible by squashing
  your commits: you can look at the [Symfony documentation](http://symfony.com/doc/current/cmf/contributing/commits.html)
  or at the [Git book](http://git-scm.com/book/en/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages)
  for more information on how to do that.

For further information on how to contribute please refer to
[our documentation](http://thefubhy.gitbooks.io/rules/contributing/README.md).

## Executing the automated tests

This module comes with PHPUnit and Simpletest tests. You need a working Drupal 8
installation and a checkout of the Rules module in the modules folder.

#### PHPUnit

    cd /path/to/drupal-8/core
    ./vendor/bin/phpunit ../modules/rules

#### Simpletest

    php ./core/scripts/run-tests.sh --verbose --color "rules"

Example for executing one single test file during development:

    php ./core/scripts/run-tests.sh --verbose --color --class "Drupal\rules\Tests\RulesEngineTest"

You can also execute the test cases from the web interface at
``/admin/config/development/testing``.
