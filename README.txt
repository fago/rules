
--------------------------------------------------------------------------------
                                 Rules
--------------------------------------------------------------------------------

Maintainers:
 * Wolfgang Ziegler (fago), nuppla@zites.net

The Rules module allows site administrators to define conditionally executed
actions based on occurring events (ECA-rules).

Poject homepage: http://drupal.org/project/rules


Installation
------------

*Before* starting, make sure that you have read at least the introduction - so
you know at least the basic concepts. You can find it here:

                      http://drupal.org/node/298480

 * Rules depends on the Entity API module, download and install it from
   http://drupal.org/project/entity
 * Copy the whole rules directory to your modules directory
   (e.g. DRUPAL_ROOT/sites/all/modules) and activate the Rules and Rules UI
   modules.
 * The administrative user interface can be found at admin/config/workflow/rules


Documentation
-------------
* Check out the general docs at http://drupal.org/node/298476
* Check out the developer targeted docs at http://drupal.org/node/878718


Rules Scheduler
---------------

 * If you enable the Rules scheduler module, you get new actions that allow you
   to schedule the execution of Rules components.
 * Make sure that you have configured cron for your drupal installation as cron
   is used for scheduling the Rules components. For help see
   http://drupal.org/cron
 * If the Views module (http://drupal.org/project/views) is installed, the module
   displays the list of scheduled tasks in the UI.
