<?php


/**
  * SquirrelMail Compose Extras Plugin
  *
  * Copyright (c) 2005-2012 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2003-2004 Justus Pendleton <justus@ryoohki.net>
  * Copyright (c) 2003 Bruce Richardson <itsbruce@uklinux.net>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage compose_extras
  *
  */



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function compose_extras_check_configuration()
{

   // make sure base config is available
   //
   if (!compose_extras_init())
   {
      do_err('Compose Extras plugin is missing its main configuration file', FALSE);
      return TRUE;
   }

}



/**
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function compose_extras_init()
{

   if (!@include_once (SM_PATH . 'config/config_compose_extras.php'))
      if (!@include_once (SM_PATH . 'plugins/compose_extras/config.php'))
         if (!@include_once (SM_PATH . 'plugins/compose_extras/config_default.php'))
            return FALSE;

   return TRUE;

/* ----------  This is how to do the same thing using the Compatibility plugin
   return load_config('compose_extras',
                      array('../../config/config_compose_extras.php',
                            'config.php',
                            'config_default.php'),
                      TRUE, TRUE);
----------- */

}



/**
  * Integrate options into SM options page
  *
  */
function ce_show_options($args)
{

   global $allow_acceses_keys, $ce_default_subject_warning;
   compose_extras_init();

   $hook_name = get_current_hook_name($args);

   // 1.4.x - 1.5.0:  options go on display options page
   // 1.5.1 and up:  options go on compose options page
   //
   if (check_sm_version(1, 5, 1) && $hook_name != 'optpage_loadhook_compose')
      return;
   if (!check_sm_version(1, 5, 1) && $hook_name != 'optpage_loadhook_display')
      return;


   // placement in option groups differs per SM version
   //
   if ($hook_name == 'optpage_loadhook_display')
   {
      $tabsIndex = 2;
      $blankLinesIndex = 2;
      $rewrapIndex = 2;
      $removeCitationIndex = 2;
   }
   else
   {
      $tabsIndex = 0;
      $blankLinesIndex = 1;
      $rewrapIndex = 0;
      $removeCitationIndex = 0;
   }


   global $data_dir, $username;
   $tabs = getPref($data_dir, $username, 'fix_compose_tabs', 1);
   $insert_lines_in_reply_body = getPref($data_dir, $username, 'insert_lines_in_reply_body', 2);
   $subject_warning = getPref($data_dir, $username, 'subject_warning', $ce_default_subject_warning);
   $rewrap_compose_body = getPref($data_dir, $username, 'rewrap_compose_body', 1);
   $remove_citation_button = getPref($data_dir, $username, 'remove_citation_button', 0);

   sq_change_text_domain('compose_extras');

   global $optpage_data;
   $optpage_data['vals'][$tabsIndex][] = array(
      'name'          => 'fix_compose_tabs',
      'caption'       => _("Compose Window Tab Order"),
      'type'          => SMOPT_TYPE_STRLIST,
      'initial_value' => $tabs,
      'refresh'       => SMOPT_REFRESH_NONE,
      'posvals'       => array(0 => _("Default"),
                               1 => _("To-Subject-Message"),
                               2 => _("To-Cc-Bcc-Subject-Message")),
   );

   $optpage_data['vals'][$blankLinesIndex][] = array(
      'name'          => 'insert_lines_in_reply_body',
      'caption'       => _("Insert Blank Lines At Top Of Reply Body"),
      'type'          => SMOPT_TYPE_STRLIST,
      'initial_value' => $insert_lines_in_reply_body,
      'refresh'       => SMOPT_REFRESH_NONE,
      'posvals'       => array(0 => 0,
                               1 => 1,
                               2 => 2,
                               3 => 3,
                               4 => 4,
                               5 => 5),
   );

   $optpage_data['vals'][$rewrapIndex][] = array(
      'name'          => 'subject_warning',
      'caption'       => _("Warn When Sending Without A Subject"),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'initial_value' => $subject_warning,
      'refresh'       => SMOPT_REFRESH_NONE,
   );

   $optpage_data['vals'][$rewrapIndex][] = array(
      'name'          => 'rewrap_compose_body',
      'caption'       => _("Show Rewrap Compose Body Button"),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'initial_value' => $rewrap_compose_body,
      'refresh'       => SMOPT_REFRESH_NONE,
   );

   $optpage_data['vals'][$removeCitationIndex][] = array(
      'name'          => 'remove_citation_button',
      'caption'       => _("Show Remove Citation Button"),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'initial_value' => $remove_citation_button,
      'refresh'       => SMOPT_REFRESH_NONE,
   );

   if ($allow_acceses_keys && !check_sm_version(1, 5, 2))
   {
      $a_to_z = array(
                   'NONE' => _("Not used"),
                   'a' => 'a', 'b' => 'b', 'c' => 'c', 'd' => 'd',
                   'e' => 'e', 'f' => 'f', 'g' => 'g', 'h' => 'h',
                   'i' => 'i', 'j' => 'j', 'k' => 'k', 'l' => 'l',
                   'm' => 'm', 'n' => 'n', 'o' => 'o', 'p' => 'p',
                   'q' => 'q', 'r' => 'r', 's' => 's', 't' => 't',
                   'u' => 'u', 'v' => 'v', 'w' => 'w', 'x' => 'x',
                   'y' => 'y', 'z' => 'z',
                );

      $accesskey_compose_identity = getPref($data_dir, $username, 'accesskey_compose_identity', 'f');
      $accesskey_compose_to = getPref($data_dir, $username, 'accesskey_compose_to', 't');
      $accesskey_compose_cc = getPref($data_dir, $username, 'accesskey_compose_cc', 'c');
      $accesskey_compose_bcc = getPref($data_dir, $username, 'accesskey_compose_bcc', 'o');
      $accesskey_compose_subject = getPref($data_dir, $username, 'accesskey_compose_subject', 'j');
      $accesskey_compose_priority = getPref($data_dir, $username, 'accesskey_compose_priority', 'p');
      $accesskey_compose_on_read = getPref($data_dir, $username, 'accesskey_compose_on_read', 'r');
      $accesskey_compose_on_delivery = getPref($data_dir, $username, 'accesskey_compose_on_delivery', 'v');
      $accesskey_compose_signature = getPref($data_dir, $username, 'accesskey_compose_signature', 'g');
      $accesskey_compose_addresses = getPref($data_dir, $username, 'accesskey_compose_addresses', 'a');
      $accesskey_compose_save_draft = getPref($data_dir, $username, 'accesskey_compose_save_draft', 'd');
      $accesskey_compose_send = getPref($data_dir, $username, 'accesskey_compose_send', 's');
      $accesskey_compose_body = getPref($data_dir, $username, 'accesskey_compose_body', 'b');
      $accesskey_compose_attach_browse = getPref($data_dir, $username, 'accesskey_compose_attach_browse', 'w');
      $accesskey_compose_attach = getPref($data_dir, $username, 'accesskey_compose_attach', 'h');
      $accesskey_compose_delete_attach = getPref($data_dir, $username, 'accesskey_compose_delete_attach', 'l');

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_identity',
         'initial_value' => $accesskey_compose_identity,
         'caption' => _("From Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_to',
         'initial_value' => $accesskey_compose_to,
         'caption' => _("To Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_cc',
         'initial_value' => $accesskey_compose_cc,
         'caption' => _("Cc Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_bcc',
         'initial_value' => $accesskey_compose_bcc,
         'caption' => _("Bcc Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_subject',
         'initial_value' => $accesskey_compose_subject,
         'caption' => _("Subject Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_body',
         'initial_value' => $accesskey_compose_body,
         'caption' => _("Body Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_priority',
         'initial_value' => $accesskey_compose_priority,
         'caption' => _("Priority Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_on_read',
         'initial_value' => $accesskey_compose_on_read,
         'caption' => _("On Read Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_on_delivery',
         'initial_value' => $accesskey_compose_on_delivery,
         'caption' => _("On Delivery Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_signature',
         'initial_value' => $accesskey_compose_signature,
         'caption' => _("Signature Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_addresses',
         'initial_value' => $accesskey_compose_addresses,
         'caption' => _("Addresses Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_save_draft',
         'initial_value' => $accesskey_compose_save_draft,
         'caption' => _("Save Draft Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_send',
         'initial_value' => $accesskey_compose_send,
         'caption' => _("Send Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_attach_browse',
         'initial_value' => $accesskey_compose_attach_browse,
         'caption' => _("Attachment Browse Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_attach',
         'initial_value' => $accesskey_compose_attach,
         'caption' => _("Add Attachment Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

      $optpage_data['vals'][2][] = array(
         'name'    => 'accesskey_compose_delete_attach',
         'initial_value' => $accesskey_compose_delete_attach,
         'caption' => _("Delete Attachment Access Key"),
         'type'    => SMOPT_TYPE_STRLIST,
         'refresh' => SMOPT_REFRESH_NONE,
         'posvals' => $a_to_z,
      );

   }

   sq_change_text_domain('squirrelmail');

}



/**
  * Inserts javascript for tab fixes (and more)
  *
  */
function ce_compose_bottom()
{

   global $username, $data_dir, $javascript_on, $allow_acceses_keys,
          $ce_limit_submit, $ce_disable_recipient_fields,
          $ce_prevent_enter_causing_submit, $ce_default_subject_warning;


   compose_extras_init();


   if (!$javascript_on)
      return;


   $output = '';


   // make submit button disable itself when clicked once
   //
/* too bad... when form widgets are disabled, they are not sent in with the POST,
   making this code unusable, since SM looks for the button that was clicked by name

   echo "<script language='JavaScript' type='text/javascript'>\n"
      . "<!--\n"
      . "document.compose.onsubmit = disableSendBtn;\n"
      . "function disableSendBtn() {\n"
      . "  document.compose.send.disabled = true;\n"
      . "  document.compose.sigappend.disabled = true;\n"
      . "  document.compose.draft.disabled = true;\n"
      . "  document.compose.attach.disabled = true;\n"
      . "  if (typeof(document.compose.html_addr_search) != 'undefined')\n"
      . "      document.compose.html_addr_search.disabled = true;\n"
      . "  if (typeof(document.compose.open_chars) != 'undefined')\n"
      . "      document.compose.open_chars.disabled = true;\n"
      . "  if (typeof(document.compose.QScancel) != 'undefined')\n"
      . "      document.compose.QScancel.disabled = true;\n"
      . "  if (typeof(document.compose.template_button) != 'undefined')\n"
      . "      document.compose.template_button.disabled = true;\n"
      . "  if (typeof(document.compose.check_spelling) != 'undefined')\n"
      . "      document.compose.check_spelling.disabled = true;\n"
//      . "  if (typeof(document.compose.xxx) != 'undefined')\n"
//      . "      document.compose.xxx.disabled = true;\n"
      . "}\n"
      . "\n// -->\n</script>\n";
*/


   // this should do the job.  only problem is if another plugin also 
   // defined an onsubmit for the compose form... sigh
   //
   if ($ce_limit_submit)
   {

      global $warning_text;
      sq_change_text_domain('compose_extras');
      $warning_text = _("Your request has already been submitted.  It will be processed shortly.");

      if (check_sm_version(1, 5, 2))
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         ob_start();
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/limit_submit.tpl');
         $output .= ob_get_contents();
         ob_end_clean();
      }
      else
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/limit_submit.tpl');
      }

      sq_change_text_domain('squirrelmail');

   }


   $tabs = getPref($data_dir, $username, 'fix_compose_tabs', 1);


   if ($tabs == 1 || $tabs == 2) 
   {

      global $location_of_buttons, $submit_button_name, $use_custom_from_tab_order;

      // if the Custom From plugin is in use and enabled by the user,
      // we need a slightly different tab ordering
      //
      $use_custom_from_tab_order = FALSE;
      global $plugins;
      if (in_array('custom_from', $plugins))
      {
         include_once(SM_PATH . 'plugins/custom_from/functions.php');
         if (custom_from_is_allowed_and_is_enabled($username))
            $use_custom_from_tab_order = TRUE;
      }

      if (check_sm_version(1, 5, 2))
         $submit_button_name = 'send1';
      else
         if ($location_of_buttons == 'bottom')
            $submit_button_name = 'send';
         else
            $submit_button_name = 'send[1]';

      if (check_sm_version(1, 5, 2))
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         ob_start();
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/tabs' . $tabs . '.tpl');
         $output .= ob_get_contents();
         ob_end_clean();
      }
      else
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/tabs' . $tabs . '.tpl');
      }

   }
   

   // add access keys if needed (as of 1.5.2, this is in the core)
   //
   if ($allow_acceses_keys && !check_sm_version(1, 5, 2))
   {
      global $location_of_buttons, $submit_button_name, $accesskey_compose_identity,
             $accesskey_compose_to, $accesskey_compose_cc, $accesskey_compose_bcc,
             $accesskey_compose_subject, $accesskey_compose_priority,
             $accesskey_compose_on_read, $accesskey_compose_on_delivery,
             $accesskey_compose_signature, $accesskey_compose_addresses,
             $accesskey_compose_save_draft, $accesskey_compose_send,
             $accesskey_compose_body, $accesskey_compose_attach_browse,
             $accesskey_compose_attach, $accesskey_compose_delete_attach;

      $accesskey_compose_identity = getPref($data_dir, $username, 'accesskey_compose_identity', 'f');
      $accesskey_compose_to = getPref($data_dir, $username, 'accesskey_compose_to', 't');
      $accesskey_compose_cc = getPref($data_dir, $username, 'accesskey_compose_cc', 'c');
      $accesskey_compose_bcc = getPref($data_dir, $username, 'accesskey_compose_bcc', 'o');
      $accesskey_compose_subject = getPref($data_dir, $username, 'accesskey_compose_subject', 'j');    
      $accesskey_compose_priority = getPref($data_dir, $username, 'accesskey_compose_priority', 'p');  
      $accesskey_compose_on_read = getPref($data_dir, $username, 'accesskey_compose_on_read', 'r'); 
      $accesskey_compose_on_delivery = getPref($data_dir, $username, 'accesskey_compose_on_delivery', 'v');
      $accesskey_compose_signature = getPref($data_dir, $username, 'accesskey_compose_signature', 'g');
      $accesskey_compose_addresses = getPref($data_dir, $username, 'accesskey_compose_addresses', 'a');
      $accesskey_compose_save_draft = getPref($data_dir, $username, 'accesskey_compose_save_draft', 'd');
      $accesskey_compose_send = getPref($data_dir, $username, 'accesskey_compose_send', 's');
      $accesskey_compose_body = getPref($data_dir, $username, 'accesskey_compose_body', 'b');
      $accesskey_compose_attach_browse = getPref($data_dir, $username, 'accesskey_compose_attach_browse', 'w');  
      $accesskey_compose_attach = getPref($data_dir, $username, 'accesskey_compose_attach', 'h');      
      $accesskey_compose_delete_attach = getPref($data_dir, $username, 'accesskey_compose_delete_attach', 'l');  

      if (check_sm_version(1, 5, 2))
         $submit_button_name = 'send1';
      else
         if ($location_of_buttons == 'bottom')
            $submit_button_name = 'send';
         else
            $submit_button_name = 'send[1]';

      global $t;
      $t = array(); // no need to put config vars herein, they are already globalized
      include_once(SM_PATH . 'plugins/compose_extras/templates/default/accesskeys.tpl');
   }


   // disable recipient input text fields?
   //
   if ($ce_disable_recipient_fields)
   {

      if (check_sm_version(1, 5, 2))
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         ob_start();
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/disable_recipients.tpl');
         $output .= ob_get_contents();
         ob_end_clean();
      }
      else
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/disable_recipients.tpl');
      }

   }


   // function that checks for an empty subject
   //
   if (getPref($data_dir, $username, 'subject_warning', $ce_default_subject_warning))
   {

      global $subject_warning_text;
      sq_change_text_domain('compose_extras');
      $subject_warning_text = _("Warning: You did not enter a subject.\\n\\nSend without a subject?");


      if (check_sm_version(1, 5, 2))
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         ob_start();
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/subject_warning.tpl');
         $output .= ob_get_contents();
         ob_end_clean();
      }
      else
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/subject_warning.tpl');
      }

      sq_change_text_domain('squirrelmail');

   }


   // prevent Enter from causing form submit from input text fields?
   //
   if ($ce_prevent_enter_causing_submit)
   {

      if (check_sm_version(1, 5, 2))
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         ob_start();
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/prevent_enter_in_text.tpl');
         $output .= ob_get_contents();
         ob_end_clean();
      }
      else
      {
         global $t;
         $t = array(); // no need to put config vars herein, they are already globalized
         include_once(SM_PATH . 'plugins/compose_extras/templates/default/prevent_enter_in_text.tpl');
      }

   }


   // for SquirrelMail versions 1.5.2+ we still need to hand back our output
   //
   if (check_sm_version(1, 5, 2))
      return array('compose_bottom' => $output);

}



/**
  * Adjusts body text as needed and add checks
  * to the page submit action (subject warning
  * and prevent multiple form submit)
  *
  */
function ce_fix_body()
{

   global $username, $data_dir, $ce_limit_submit, $compose_onsubmit,
          $action, $ce_default_subject_warning;
   $onsubmit = array();


   compose_extras_init();


   // stop user from being able to double-click submit button
   // (IMPORTANT that this comes BEFORE the subject warning)
   //
   if ($ce_limit_submit)
   {
      $text = ' if (!submitOnlyOnce()) return false; ';

      if (check_sm_version(1, 4, 21))
         $compose_onsubmit[] = $text;
      else
         $onsubmit[] = $text;
   }


   // optionally warn user if sending without a subject
   //
   if (getPref($data_dir, $username, 'subject_warning', $ce_default_subject_warning))
   {
      $text = ' if (!check_subject()) {submit_count = 0; return false; } ';

      if (check_sm_version(1, 4, 21))
         $compose_onsubmit[] = $text;
      else
         $onsubmit[] = $text;
   }


   // pre-1.4.21
   //
   if (!empty($onsubmit))
      echo ' onsubmit="' . implode('', $onsubmit) . '" ';


   // in 1.5.2+, $action is already figured out and in the global scope
   // otherwise (1.4.x), get from the POST/GET
   //
   if (empty($action))
      sqgetGlobalVar('smaction', $action, SQ_FORM);


   if ($action != 'reply' && $action != 'reply_all' && $action != 'forward')
//    && $action != 'draft' && $action != 'edit_as_new')
      return;


   $insert_lines_in_reply_body = getPref($data_dir, $username, 'insert_lines_in_reply_body', 2);


   if ($insert_lines_in_reply_body < 1)
      return;

   global $body;

   for ($i = 0; $i <= $insert_lines_in_reply_body; $i++)
      $body = "\n" . $body;

}



/**
  * Include JavaScript that accomplishes the rewrap functionality.
  * Also, make sure this plugin comes before all others on the
  * compose_form hook so it can prevent other onsubmit handlers
  * from firing multiple times.
  *
  */
function rewrap_add_script($args)
{

   // only need to bother when we are on the compose page
   //
   if (defined('PAGE_NAME'))
   {
      if (PAGE_NAME != 'compose') return;
   }
   else
   {
      global $PHP_SELF;
      if (strpos($PHP_SELF, '/src/compose') === FALSE)
         return;
   }


   // Make sure this plugin fires FIRST on the compose_form hook
   //
   reposition_plugin_on_hook('compose_extras', 'compose_form', TRUE);


   // can bail when javascript is de-activated/not
   // in use or when user has functionality disabled
   //
   global $javascript_on, $data_dir, $username;
   if (!$javascript_on) return;
   $rewrap_compose_body = getPref($data_dir, $username, 'rewrap_compose_body', 1);
   if (!$rewrap_compose_body) return;


   // ok to proceed to output the needed script link
   //
   $script = '<script type="text/javascript" language="JavaScript" src="' . sqm_baseuri() . 'plugins/compose_extras/rewrap.js"></script>';


   // get script tag out correctly depending on SM version
   //
   if (check_sm_version(1, 5, 2))
   {
      $args[0] .= $script;
   }
   else
   {
      echo "\n" . $script . "\n";
   }

}



/**
  * Add the "Rewrap" button to the compose screen
  *
  */
function rewrap_add_button()
{

   // only need to bother when javascript is activated/in use
   // and when one of the buttons is turned on
   //
   global $javascript_on, $data_dir, $username;
   if (!$javascript_on) return;
   $rewrap_compose_body = getPref($data_dir, $username, 'rewrap_compose_body', 1);
   $remove_citation_button = getPref($data_dir, $username, 'remove_citation_button', 0);
   if (!$rewrap_compose_body && !$remove_citation_button) return;


   sq_change_text_domain('compose_extras');


   // remove citation button
   //
   global $body_quote;
   $citation = $body_quote . '[ \t\v\f]*';
   $citation_button_script = 'javascript:this.form.body.value=removeCitation(this.form.body.value, \'' . $citation . '\')';
   $citation_button_value = _("Remove Citation");


   // rewrap button
   //
   $rewrap_button_script = 'javascript:this.form.body.value=sq_rewrap(this.form.body.value, this.form.body.cols)';
   $rewrap_button_value = _("Rewrap");


   sq_change_text_domain('squirrelmail');


   if (check_sm_version(1, 5, 2))
   {

      global $oTemplate, $nbsp;
      $output = '';

      // don't show remove citation button if there is no body quote
      //
      if ($remove_citation_button && !empty($body_quote))
         $output .= addButton($citation_button_value, '',
                             array('onclick' => $citation_button_script))
                 . $nbsp;
      if ($rewrap_compose_body)
         $output .= addButton($rewrap_button_value, '',
                             array('onclick' => $rewrap_button_script))
                 . $nbsp;

      return array('compose_button_row' => $output);

   }
   else
   {

      // don't show remove citation button if there is no body quote
      //
      if ($remove_citation_button && !empty($body_quote))
         echo '<input type="button" value="' . $citation_button_value
            . '" onclick="' . $citation_button_script . '">' . "\n";

      if ($rewrap_compose_body)
         echo '<input type="button" value="' . $rewrap_button_value
            . '" onclick="' . $rewrap_button_script . '">' . "\n";

   }


}



