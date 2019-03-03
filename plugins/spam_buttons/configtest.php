<?php

/**
  * SquirrelMail Spam Buttons Plugin
  * Copyright (c) 2005-2009 Paul Lesniewski <paul@squirrelmail.org>,
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage spam_buttons
  *
  */



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function spam_buttons_check_configuration_do()
{

   include_once(SM_PATH . 'plugins/spam_buttons/functions.php');

   global $is_spam_resend_destination, $is_not_spam_resend_destination,
          $is_spam_shell_command, $is_not_spam_shell_command,
          $show_not_spam_button, $show_is_spam_button,
          $sb_report_spam_by_move_to_folder,
          $sb_report_not_spam_by_move_to_folder,
          $sb_report_spam_by_custom_function,
          $sb_report_not_spam_by_custom_function;


   // make sure compatibility plugin is there at all (have to do this
   // because the spam_buttons_init() function uses load_config()
   //
   if (!function_exists('check_file_contents'))
   {
      do_err('Spam Buttons plugin requires the Compatibility plugin version 2.0.12+', FALSE);
      return TRUE;
   }



   // make sure base config is available
   //
   if (!spam_buttons_init())
   {
      do_err('Spam Buttons plugin is missing its main configuration file', FALSE);
      return TRUE;
   }



   // check that the needed patch is in place for SM versions < 1.4.11
   //
   if (!check_sm_version(1, 4, 11))
   {
      if (!check_file_contents(SM_PATH . 'functions/mailbox_display.php', 'do_hook\(\'mailbox_display_buttons\'\);'))
      {
         do_err('Spam Buttons plugin requires a patch with your version of SquirrelMail, but it has not been applied', FALSE);
         return TRUE;
      }
   }



   $spam_methods = 0;
   if (!empty($sb_report_spam_by_move_to_folder)) $spam_methods++;
   if (!empty($is_spam_shell_command)) $spam_methods++;
   if (!empty($is_spam_resend_destination)) $spam_methods++;
   if (!empty($sb_report_spam_by_custom_function)) $spam_methods++;

   $ham_methods = 0;
   if (!empty($sb_report_not_spam_by_move_to_folder)) $ham_methods++;
   if (!empty($is_not_spam_shell_command)) $ham_methods++;
   if (!empty($is_not_spam_resend_destination)) $ham_methods++;
   if (!empty($sb_report_not_spam_by_custom_function)) $ham_methods++;


   // make sure "Is Spam" reporting method is properly configured
   //
   if ($show_is_spam_button && $spam_methods == 0)
   {
      do_err('Spam Buttons plugin is configured to show the "Is Spam" button, but there is no reporting method configured.  Please specify either $sb_report_spam_by_move_to_folder, $is_spam_shell_command, $is_spam_resend_destination or $sb_report_spam_by_custom_function', FALSE);
      return TRUE;
   }


   // make sure "Is Not Spam" reporting method is properly configured
   //
   if ($show_not_spam_button && $ham_methods == 0)
   {
      do_err('Spam Buttons plugin is configured to show the "Is Not Spam" button, but there is no reporting method configured.  Please specify either $sb_report_not_spam_by_move_to_folder, $is_not_spam_shell_command, $is_not_spam_resend_destination or $sb_report_not_spam_by_custom_function', FALSE);
      return TRUE;
   }


//TODO: the statement below does not seem to be correct...??
/* we now allow more than one reporting type ---
   // make sure "Is Spam" reporting method is not "overly" configured
   //
   if ($show_is_spam_button && $spam_methods > 1)
   {
      do_err('Spam Buttons plugin is configured to show the "Is Spam" button, but more than one reporting method has been specified.  Please choose $sb_report_spam_by_move_to_folder, $is_spam_shell_command, $is_spam_resend_destination or $sb_report_spam_by_custom_function, but not more than one', FALSE);
      return TRUE;
   }


   // make sure "Is Not Spam" reporting method is not "overly" configured
   //
   if ($show_not_spam_button && $ham_methods > 1)
   {
      do_err('Spam Buttons plugin is configured to show the "Is Not Spam" button, but more than one reporting method has been specified.  Please choose $sb_report_not_spam_by_move_to_folder, $is_not_spam_shell_command, $is_not_spam_resend_destination or $sb_report_not_spam_by_custom_function, but not more than one', FALSE);
      return TRUE;
   }
//TODO: the statement below does not seem to be correct...??
--- we now allow more than one reporting type */



//TODO: I think we could also verify similar things as below for any extra buttons too?
   // check that custom reporting functions have been implemented
   //
   if (!empty($sb_report_spam_by_custom_function) && !function_exists($sb_report_spam_by_custom_function))
   {
      do_err('Spam Buttons plugin is configured to report spam by the use of a custom PHP function, but that function was not found.  Please check $sb_report_spam_by_custom_function and that it points to a valid PHP function that is defined or loaded in the Spam Buttons configuration file', FALSE);
      return TRUE;
   }
   if (!empty($sb_report_not_spam_by_custom_function) && !function_exists($sb_report_not_spam_by_custom_function))
   {
      do_err('Spam Buttons plugin is configured to report ham (non-spam) by the use of a custom PHP function, but that function was not found.  Please check $sb_report_not_spam_by_custom_function and that it points to a valid PHP function that is defined or loaded in the Spam Buttons configuration file', FALSE);
      return TRUE;
   }



   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically
   //
   if (!check_sm_version(1, 5, 2))
   {

      // try to find Compatibility, and then that it is v2.0.12+
      //
      if (function_exists('check_plugin_version')
       && check_plugin_version('compatibility', 2, 0, 12, TRUE))
      { /* no-op */ }


      // something went wrong
      //
      else
      {
         do_err('Spam Buttons plugin requires the Compatibility plugin version 2.0.12+', FALSE);
         return TRUE;
      }

   }


   // check for exec being disabled
   //
   $disable_functions = explode(',', ini_get('disable_functions'));
   if (in_array('exec', $disable_functions)
    && (!empty($is_spam_shell_command) || !empty($is_not_spam_shell_command)))
   {
      do_err('You have disabled the "exec" command in your PHP configuration and are using the report-by-shell-command method for the Spam Buttons plugin.  Spam and/or Ham reports will not work until you fix one or the other.', FALSE);
      return TRUE;
   }


   // check for problems when in safe_mode
   //
   if (ini_get('safe_mode') 
    && (!empty($is_spam_shell_command) || !empty($is_not_spam_shell_command)))
   {
      $safe_mode_exec_dir = ini_get('safe_mode_exec_dir');
      do_err('You have safe_mode enabled in your PHP configuration and are using the report-by-shell-command method for the Spam Buttons plugin.  Please double check that your reporting command is in an allowable directory and has ownership that allows access by the web server.  (Your safe_mode_exec_dir is "' . $safe_mode_exec_dir . '".)', FALSE);
      return FALSE;
   }


   return FALSE;

}



