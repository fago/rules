$Id$

Rules Module
------------
by Wolfgang Ziegler, nuppla@zites.net


The rules modules allows site administrators to define conditionally executed actions
 based on occurring events (ECA-rules). It's a replacement with more features for the
 trigger module in core and the successor of the workflow-ng module.

It opens new opportunities for site builders to extend the site in ways not possible
before.



Installation
-------------

*Before* starting, make sure that you have read at least the introduction - so you know 
at least the basic concepts. You can find it here:
                     
                          http://drupal.org/node/156288

 * Copy the whole rules directory to your modules directory and
   activate the rules modules.
 * You can find the admin interface at /admin/rules.

Notes:
 * If you have the php module activated, you can use a php input evaluator in 
   your rules.
 * If you install the token module, you can use token replacements in your rules.
   http://drupal.org/project/token
 

 
Upgrade from Workflow-ng
---------------------------
You can easily upgrade from workflow-ng (5.x) installations to 6.x. First off install
the rules module as usual, then go to update.php and run update 6001 - which is the 
upgrade procedure. It will detect the old workflow-ng installation and convert the 
workflow-ng rules to rules.

Furthermore you can import exported workflow-ng rules with the normal rules import tool.
It's a good idea to export all workflow-ng rules before upgrading to rules, so you can
import an old workflow-ng at any time.


Important notes when converting a workflow-ng rule:

  * When converting a workflow-ng rule, you need to have support for all used
    events, conditions or actions in 6.x too - otherwise the rule
    conversion will fail.
    
    E.g. if you have used actions provided by the og module, install and upgrade
    the og module first, then convert the rule.
    
    When the module conversion fails, make sure you have all involved modules upgraded
    and try again. 


  * Modules may help upgrading provided actions. E.g. the rules module provides
    an upgrade path for upgrading from the workflow-ng "Make content (un)sticky"
    action to the two core "Make content sticky" or "Make content unsticky" actions.

    If a module has changed its actions, but doesn't provide an upgrade path, file
    a bug report / feature request for this module. 

  * The workflow-ng extension modules are not yet ported.
