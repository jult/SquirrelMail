<?php
/**
 * setup.php
 * -----------
 * Squirrelspell setup file, as defined by the SquirrelMail-1.2 API.
 *
 * Copyright (c) 1999-2019 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @author Konstantin Riabitsev <icon@duke.edu>
 * @version $Id: setup.php 14800 2019-01-08 04:27:15Z pdontthink $
 * @package plugins
 * @subpackage squirrelspell
 */

/** @ignore */
if (! defined('SM_PATH')) define('SM_PATH','../../');

/**
 * Standard SquirrelMail plugin initialization API.
 *
 * @return void
 */
function squirrelmail_plugin_init_squirrelspell() {
  global $squirrelmail_plugin_hooks;
  $squirrelmail_plugin_hooks['compose_button_row']['squirrelspell'] =
      'squirrelspell_setup';
  $squirrelmail_plugin_hooks['optpage_register_block']['squirrelspell'] =
      'squirrelspell_optpage_register_block';
}

/**
 * This function formats and adds the plugin and its description to the
 * Options screen.
 *
 * @return void
 */
function squirrelspell_optpage_register_block() {
  global $optpage_blocks, $javascript_on, $squirrelmail_language;
  if ($javascript_on) {
    // this is a hack to avoid having to change the strings
    // in all our translations for this misspelled word
    if (strpos($squirrelmail_language, 'en_') === 0)
        $name = 'Spell Checker Options';
    else
        $name = _("SpellChecker Options");
    $optpage_blocks[] =
      array(
        'name' => $name,
        'url'  => '../plugins/squirrelspell/sqspell_options.php',
        'desc' => _("Here you may set up how your personal dictionary is stored, edit it, or choose which languages should be available to you when spell-checking."),
        'js'   => TRUE);
  }
}

/**
 * This function adds a "Check Spelling" link to the "Compose" row
 * during message composition.
 *
 * @return void
 */
function squirrelspell_setup() {
    global $data_dir, $username, $javascript_on;
    $sqspell_show_button = getPref($data_dir, $username, 'sqspell_show_button', 1);
    if ($javascript_on && $sqspell_show_button) {
        echo '<input type="button" value="'
           . _("Check Spelling")
           . '" name="check_spelling" onclick="window.open(\'../plugins/squirrelspell/sqspell_interface.php\', \'sqspell\', \'status=yes,width=550,height=370,resizable=yes\')" />';
    }
}

