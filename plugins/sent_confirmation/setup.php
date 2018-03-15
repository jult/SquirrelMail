<?php


/**
  * SquirrelMail Sent Confirmation Plugin
  * Copyright (C) 2004 Paul Lesneiwski <pdontthink@angrynerds.com>
  * This program is licensed under GPL. See COPYING for details
  *
  */


function squirrelmail_plugin_init_sent_confirmation()
{

	global $squirrelmail_plugin_hooks;

        $squirrelmail_plugin_hooks['compose_send']['sent_confirmation']            = 'sent_conf_message_sent';
        $squirrelmail_plugin_hooks['generic_header']['sent_confirmation']          = 'sent_conf_check_is_sent';

// This was causing PHP warnings when replying so don't use 
// it for now - it wasn't really necessary, just to be extra 
// extra safe anyway (turn off sent flag)
// Update: it does provide an erroneous "message sent" notification
// when SMTP errors occur, so instead, the sent_conf_status is reset
// in the check_is_sent_do function whenever the sent_conf screen
// isn't being shown - seems to work fine
//        $squirrelmail_plugin_hooks['compose_bottom']['sent_confirmation']          = 'sent_conf_compose_bottom';


        $squirrelmail_plugin_hooks['options_display_inside']['sent_confirmation']  = 'sent_conf_show_options';
        $squirrelmail_plugin_hooks['options_display_save']['sent_confirmation']    = 'sent_conf_save_options';


}



// Version information
//
function sent_confirmation_version() 
{

   return '1.6';

}



if (!defined('SM_PATH'))
   define('SM_PATH', '../');



// This is the text that appears on the option page
//
function sent_conf_show_options() 
{

    include_once(SM_PATH . 'plugins/sent_confirmation/options.php');
    sent_conf_options();

}



// Here we save the user's sent_confirmation preferences
//
function sent_conf_save_options() 
{

    include_once(SM_PATH . 'plugins/sent_confirmation/options.php');
    sent_conf_options_save();

}



// set flag indicating message was sent
function sent_conf_message_sent() 
{

    include_once(SM_PATH . 'plugins/sent_confirmation/functions.php');
    sent_conf_message_sent_do();

}



// 
//
function sent_conf_check_is_sent() 
{

    include_once(SM_PATH . 'plugins/sent_confirmation/functions.php');
    sent_conf_check_is_sent_do();

}



// 
//
function sent_conf_compose_bottom() 
{

    include_once(SM_PATH . 'plugins/sent_confirmation/functions.php');
    sent_conf_compose_bottom_do();

}


?>
