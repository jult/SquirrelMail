<?php

/*
 * Plugin Functions
 */

function squirrelmail_plugin_add_file($pluginname, $filename)
{
  if (defined('SM_PATH')) {
    include_once(SM_PATH . 'plugins/' . $pluginname . '/' . $filename);
  } else {
    include_once('../plugins/' . $pluginname . '/' . $filename);
  }
}

function squirrelmail_plugin_init_show_headers() {
  global $squirrelmail_plugin_hooks;
  
  $squirrelmail_plugin_hooks['options_display_inside']['show_headers'] = 'show_headers_display_show';
  $squirrelmail_plugin_hooks['options_display_save']['show_headers'] = 'show_headers_display_save';
  $squirrelmail_plugin_hooks['loading_prefs']['show_headers'] = 'show_headers_load_prefs';
  $squirrelmail_plugin_hooks['read_body_header']['show_headers'] = 'show_headers_action';
}

function show_headers_display_show() {
  squirrelmail_plugin_add_file('show_headers', 'functions.php');
  
  show_headers_options_display();
}

function show_headers_display_save() {
  squirrelmail_plugin_add_file('show_headers', 'functions.php');

  show_headers_options_save();
}

function show_headers_load_prefs() {
  squirrelmail_plugin_add_file('show_headers', 'functions.php');

  show_headers_load();
}


function show_headers_action() {
  squirrelmail_plugin_add_file('show_headers', 'functions.php');

  show_headers_display();
}

function show_headers_version() {
  return '1.2';
}

?>
