<?php
/**
 * PROON: autoprune folders
 *
 */

function proon_version_reader($line_num) {
  $v = 'Unknown';
  $v_file = SM_PATH . 'plugins/proon/version';
  if (file_exists($v_file)) {
    $lines = file($v_file);
    if ($lines && count($lines) > $line_num) {
      $v = trim($lines[$line_num]);
    }
  }
  return $v;
}

function proon_version() {
  return proon_version_reader(1);
}

function proon_name() {
  return proon_version_reader(0);
}

function squirrelmail_plugin_init_proon() {

  global $squirrelmail_plugin_hooks;

  // This hook is used for the recurring automatic pruning stuff.  Although
  // it is called every time for this hook, internal logic tries to quickly
  // determine if no action is actually needed and return in that case.

  $squirrelmail_plugin_hooks['right_main_bottom']['proon'] = 'proon_prune_hook_function_STUB';

  // This hook puts a link on the folder options page leading to the proon
  // page.  We use a separate page because the options are fairly elaborate.
  // CHANGED:  now a stand-alone options page to overcome a webmail.php change
  //
  // It also conditionally puts a button at the bottom of the left frame
  // leading to the same place.  (We use the same hook function and it does
  // different (but related) things depending on which hook calls it.)

  //$squirrelmail_plugin_hooks['options_folder_inside']['proon'] = 'proon_link_STUB';
  $squirrelmail_plugin_hooks['left_main_after']['proon'] = 'proon_link_STUB';
  $squirrelmail_plugin_hooks['optpage_register_block']['proon'] = 'proon_optpage_STUB';
  //$squirrelmail_plugin_hooks['optpage_set_loadinfo']['proon'] = 'proon_optpage_loadinfo_STUB';

  // This hook is responsible for conditionally converting auto_prune_sent stuff
  // into its prune equivalent.  We do a first-order condition on something cheap
  // to avoid having the hook in the usual case.  The hook can also be used to
  // force everyone to the proon settings form at least once.

  $squirrelmail_plugin_hooks['webmail_top']['proon'] = 'proon_onetime_STUB';

}

function proon_prune_hook_function_STUB($h) {
  include_once(SM_PATH . 'plugins/proon/hook.php');
  proon_prune_hook_function($h);
}

function proon_link_STUB($h) {
  include_once(SM_PATH . 'plugins/proon/hook.php');
  proon_link($h);
}

function proon_onetime_STUB($h) {
  include_once(SM_PATH . 'plugins/proon/hook.php');
  proon_onetime($h);
}

function proon_optpage_STUB($h) {
  include_once(SM_PATH . 'plugins/proon/hook.php');
  proon_optpage($h);
}

function proon_optpage_loadinfo_STUB($h) {
  include_once(SM_PATH . 'plugins/proon/hook.php');
  proon_optpage_loadinfo($h);
}

?>
