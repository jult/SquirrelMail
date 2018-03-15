<?php

/**
  * SquirrelMail Custom From Plugin
  *
  * Copyright (c) 2003-2012 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage custom_from
  *
  */



/**
  * Initialize this plugin (load config values)
  *
  * @param boolean $debug When TRUE, do not suppress errors when including
  *                       configuration files (OPTIONAL; default FALSE)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function custom_from_init($debug=FALSE)
{

   if ($debug)
   {
      if (!include_once(SM_PATH . 'config/config_custom_from.php'))
         if (!include_once(SM_PATH . 'plugins/custom_from/config/config.php'))
            if (!include_once(SM_PATH . 'plugins/custom_from/config/config_default.php'))
               return FALSE;
   }
   else
   {
      if (!@include_once(SM_PATH . 'config/config_custom_from.php'))
         if (!@include_once(SM_PATH . 'plugins/custom_from/config/config.php'))
            if (!@include_once(SM_PATH . 'plugins/custom_from/config/config_default.php'))
               return FALSE;
   }


   return TRUE;

}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function custom_from_check_configuration()
{

   global $restrict_access_to_users_file;


   // make sure base config is available
   //
   if (!custom_from_init())
   {
      do_err('Custom From plugin is missing its main configuration file', FALSE);
      return TRUE;
   }


   // if using the user access table, make sure the file exists
   //
   if (!empty($restrict_access_to_users_file)
    && !is_readable($restrict_access_to_users_file))
   {
      do_err('Custom From plugin user access table could not be found or is not readable by the web server.', FALSE);
      return TRUE;
   }

}



/**
  * See if the given user has access to and has
  * enabled the Custom From functionality
  *
  * @param string $user The username to check
  *
  * @return boolean TRUE if the user has access and
  *                 has enabled it in their personal
  *                 preferences; FALSE otherwise
  *
  */
function custom_from_is_allowed_and_is_enabled($user)
{
   global $data_dir;

   if (determine_custom_from_user_access($user))
   {
      return getPref($data_dir, $user, 'use_custom_from', 0);
   }
   return FALSE;
}



/**
  * Check if the given user has access to this plugin
  *
  * If the $restrict_access_to_users_file setting is empty,
  * all users are allowed access.  Otherwise, the setting
  * should point to a file that lists what users are
  * allowed access (glob style wildcards are acceptable).
  *
  * If the file doesn't exist or some other problem occurs,
  * no users will be able to access the Custom From functionality.
  *
  * @param string $user The username to check for access
  *
  * @return boolean TRUE if the user has access; FALSE otherwise
  *
  */
function determine_custom_from_user_access($user)
{

   global $restrict_access_to_users_file;
   custom_from_init();


   if (empty($restrict_access_to_users_file))
      return TRUE;


   // only do this once per page request
   //
   static $checked_users = array();
   if (!empty($checked_users[$user]))
   {
      if ($checked_users[$user] === 'yes') return TRUE;
      if ($checked_users[$user] === 'no') return FALSE;

      // should never get here, no need to translate this string
      echo 'FATAL ERROR: Unexpected value in list of Custom From checked users.  Contact your system administrator';
      exit;
   }


   // override per-user settings
   //
   if (!empty($restrict_access_to_users_file) && !empty($user)
    && file_exists($restrict_access_to_users_file))
   {

      // search for user
      //
      // NOTE: use of the function sq_call_function_suppress_errors()
      //       means we have to require SquirrelMail version 1.4.12+
      //       OR Compatibility plugin version 2.0.15+
      //
      if ($USERS = sq_call_function_suppress_errors('fopen', array($restrict_access_to_users_file, 'r')))
      {

         while (!feof($USERS))
         {
 
            $line = fgets($USERS, 4096);
            $line = trim($line);
 
 
            // skip blank lines and comment lines and php
            // open/close tag lines
            //
            if (strpos($line, '#') === 0 || strlen($line) < 2
             || strpos($line, '<?php') === 0 || strpos($line, '*/') === 0)
               continue;
 
 
            // stop when we have the right username (case insensitive)
            //
            if (preg_match('/^'
                         . str_replace(array('?', '*'), array('\w{1}', '.*?'), $line)
                         . '$/i', $user))
            {
               $checked_users[$user] = 'yes';
               return TRUE;
            }
 
         }
 
         fclose($USERS);

      }

   }

   $checked_users[$user] = 'no';
   return FALSE;

}



/**
  * Show options on display preferences page
  *
  */
function custom_from_display_options()
{

   global $data_dir, $username, $optpage_data;

   if (!determine_custom_from_user_access($username))
      return;

   sq_change_text_domain('custom_from');

   $use_custom_from = getPref($data_dir, $username, 'use_custom_from', 0);

   $optpage_data['vals'][2] = array_merge($optpage_data['vals'][2], array(array(
      'name'          => 'use_custom_from',
      'caption'       => _("Allow Arbitrary From Addresses"),
      'type'          => SMOPT_TYPE_BOOLEAN,
      'initial_value' => $use_custom_from,
      'refresh'       => SMOPT_REFRESH_NONE,
   )));

   sq_change_text_domain('squirrelmail');

}



/**
  * Make SquirrelMail use the correct From address
  *
  * Fire on the abook_init hook so that we can change
  * incoming data that the compose page makes use of
  *
  */
function cf_before_send()
{

   global $PHP_SELF, $username;

   // make sure we're on the compose page
   //
   if (strpos($PHP_SELF, '/compose.php') === FALSE)
      return;


   if (!custom_from_is_allowed_and_is_enabled($username))
      return;


   $custom_from = NULL;
   sqgetGlobalVar('custom_from', $custom_from, SQ_POST);
   if (!empty($custom_from))
   {
      // this tricks the code in compose.php (function deliverMessage())
      //
      global $identity, $idents;
      $idents[$identity]['email_address'] = $custom_from;
      $idents[$identity]['full_name'] = '';
//TODO: if not in email format, reply-to will be broken (no domain) - why doesn't it add default domain like email-address does? either way, we need to verify it and add the domain if needed
      $idents[$identity]['reply_to'] = $custom_from;
   }

}



/**
  * Begin buffering so we can modify compose page content later
  *
  */
function cf_start_buffering()
{

   global $username;

   if (!custom_from_is_allowed_and_is_enabled($username))
      return;

   ob_start();

}



/**
  * Modify the compose page, adding our custom input
  *
  */
function cf_modify_compose_page()
{

   global $color, $username, $javascript_on, $SQ_GLOBAL;

   if (!custom_from_is_allowed_and_is_enabled($username))
      return;

   $output = ob_get_contents();
   ob_end_clean();

   $custom_from = NULL;
   sqgetGlobalVar('custom_from', $custom_from, $SQ_GLOBAL);
   if (empty($custom_from))
   {
      global $idents, $identity;

      if (isset($identity) && !empty($idents[$identity]))
      {
         $custom_from = htmlspecialchars($idents[$identity]['full_name'])
                                         . ' <'
                                         . htmlspecialchars($idents[$identity]['email_address'])
                                         . '>';
      }

      // hmmm, no default identity - not sure that's a good thing,
      // but we'll try to look up full name and email address instead
      //
      else
      {
         global $data_dir, $username;

         $fn = getPref($data_dir, $username, 'full_name');
         $em = getPref($data_dir, $username, 'email_address');

         $custom_from = htmlspecialchars($fn);
         if ($em != '')
         {
            if($fn != '')
               $custom_from .= ' <' . htmlspecialchars($em) . '>';
            else
               $custom_from .= htmlspecialchars($em);
         }
      }
   }

   $output = preg_replace('|<select name="identity">(.*?</select>\s*</td>\s*</tr>)|s',
                          '<select name="identity" '
                          . ($javascript_on ? 'onChange="for (i = 0; i < this.length; i++) { if (this.options[i].selected) { this.form.custom_from.value = this.options[i].text; break; } }"' : '')
                          . '>$1<tr>'
                          . html_tag('td', '', 'right', $color[4], 'width="10%"')
                          // i18n: string below intentionally in SquirrelMail domain
                          . _("From:") . '</td>'
                          . html_tag('td', '', 'left', $color[4], 'width="90%"')
                          . substr(addInput('custom_from', $custom_from, 60), 0, -3)
                          . ($javascript_on ? ' onfocus="alreadyFocused=true;" ' : '')
                          . ' /><br /></td></tr>',
                          $output);
   echo $output;

}



