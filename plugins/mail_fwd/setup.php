<?php
/**
** Email Forwarding plugin for SquirrelMail
**  setup.php
**
**  Copyright (c) 1999-2002 Pontus Ullgren
**  Licensed under the GNU GPL. For full terms see the file COPYING.
**
**  Setting up the mail_fwd plugin, this file also contains the logic for
**  saving the option.
**
**/

function squirrelmail_plugin_init_mail_fwd() {
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks["options_save"]["mail_fwd"] = "mail_fwd_save_pref";
    $squirrelmail_plugin_hooks['optpage_register_block']['mail_fwd'] = 'mail_fwd_optpage_register_block';
}

function mail_fwd_optpage_register_block () {

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/mail_fwd/functions.php');
   else
      include_once('../plugins/mail_fwd/functions.php');

   mail_fwd_optpage_register_block_do();

}

function mail_fwd_save_pref() {

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/mail_fwd/functions.php');
   else
      include_once('../plugins/mail_fwd/functions.php');

   mail_fwd_save_pref_do();

}

?>
