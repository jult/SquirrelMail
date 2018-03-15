<?php

/**
  * SquirrelMail Local User Autoresponder and Mail Forwarder Plugin
  * Copyright (c) 2004-2009 Jonathan Bayer <jbayer@spamcop.net>,
  *                         Paul Lesniewski <paul@squirrelmail.org>,
  *                         Dan Astoorian
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage local_autorespond_forward
  *
  */



/**
  * Inserts link to the configuration page
  * in main SM options page
  *
  */
function laf_option_link_do() 
{

   global $optpage_blocks, $maintain_autoresponder, 
          $maintain_forwarding, $forward_file;

   local_autorespond_forward_init();

   if (!$maintain_autoresponder && !$maintain_forwarding)
      die('Local User Autoresponder and Mail Forwarder plugin cannot have both $maintain_autoresponder and $maintain_forwarding turned off');
   if (empty($forward_file) && $maintain_forwarding)
      die('Local User Autoresponder and Mail Forwarder plugin cannot have $maintain_forwarding turned on without also having $forward_file configured');

   sq_change_text_domain('local_autorespond_forward');

   if ($maintain_autoresponder)
   {

      // all functionalities turned on
      //
      if ($maintain_forwarding)
      {
         $title = _("Autoresponder / Mail Forwarding");
         $desc = _("Set up an auto-reply message and optionally forward your incoming email to other addresses. This can be useful when you are away on vacation.");
      }

      // only an autoresponder - no forwarding
      //
      else
      {
         $title = _("Autoresponder");
         $desc = _("Set up an auto-reply message for you incoming email. This can be useful when you are away on vacation.");
      }
   }

   // only mail forwarding
   //
   else
   {
      $title = _("Mail Forwarding");
      $desc = _("Set up other email addresses to which your incoming messages will be forwarded.");
   }

   $optpage_blocks[] = array(
      'name' => $title,
      'url' => sqm_baseuri() . 'plugins/local_autorespond_forward/options.php',
      'desc' => $desc,
      'js' => FALSE
   );

   sq_change_text_domain('squirrelmail');

}



/**
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function local_autorespond_forward_init()
{

   if (!@include_once(SM_PATH . 'config/config_local_autorespond_forward.php'))
      if (!@include_once(SM_PATH . 'plugins/local_autorespond_forward/config.php'))
         return FALSE;

   return TRUE;

}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function local_autorespond_forward_check_configuration_do()
{

   // make sure base config is available
   //
   if (!local_autorespond_forward_init())
   {
      do_err('Local User Autoresponder and Mail Forwarder plugin is missing its main configuration file', FALSE);
      return TRUE;
   }


   // make sure configuration is not b0rked
   //
   global $maintain_autoresponder, $maintain_forwarding, $forward_file,
          $laf_backend, $suid_binary;
   if (!$maintain_autoresponder && !$maintain_forwarding)
   {
      do_err('Local User Autoresponder and Mail Forwarder plugin cannot have both $maintain_autoresponder and $maintain_forwarding turned off', FALSE);
      return TRUE;
   }
   if (empty($forward_file) && $maintain_forwarding)
   {
      do_err('Local User Autoresponder and Mail Forwarder plugin cannot have $maintain_forwarding turned on without also having $forward_file configured', FALSE);
      return TRUE;
   }
   if ($laf_backend != 'ftp' && $laf_backend != 'suid')
   {
      do_err('Local User Autoresponder and Mail Forwarder plugin $laf_backend is misconfigured.  It must be either "ftp" or "suid".', FALSE);
      return TRUE;
   }
   if ($laf_backend == 'ftp' && !function_exists('ftp_connect'))
   {
      do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use FTP, but no FTP support is available in this PHP build.', FALSE);
      return TRUE;
   }


   // for suid backend users, check permissions on suid file
   //
   if (function_exists('get_process_owner_info')
    && $laf_backend == 'suid')
   {
      if (!file_exists($suid_binary))
      {
         do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use the set-uid (suid) backend, but $suid_binary does not point to a valid file.', FALSE);
         return TRUE;
      }

      $permissions = fileperms($suid_binary);
      $displayable_permissions = base_convert($permissions, 10, 8);
      $file_owner_number = @fileowner($suid_binary);
      if (function_exists('posix_getpwuid')
       && ($file_owner = posix_getpwuid($file_owner_number))
       && !empty($file_owner['name']))
         $file_owner = $file_owner['name'];
      else
         $file_owner = '';
      $file_group_number = @filegroup($suid_binary);
      if (function_exists('posix_getgrgid')
       && ($file_group = posix_getpwuid($file_group_number))
       && !empty($file_group['name']))
         $file_group = $file_group['name'];
      else
         $file_group = '';
      $process_info = get_process_owner_info();
      $config_message = '$suid_binary has permissions ' . $displayable_permissions . ' (should be 104755) and ownership ' . (!empty($file_owner) ? $file_owner . '(' . $file_owner_number . ')' : $file_owner_number) . '/' . (!empty($file_group) ? $file_group . '(' . $file_group_number . ')' : $file_group_number) . ' (should be root(0)/root(0))';
// suid backend is executable by anyone - it itself checks if the web server is calling it
//      $config_message = '$suid_binary has permissions ' . $displayable_permissions . ' (should be 104750) and ownership ' . (!empty($file_owner) ? $file_owner . '(' . $file_owner_number . ')' : $file_owner_number) . '/' . (!empty($file_group) ? $file_group . '(' . $file_group_number . ')' : $file_group_number) . ' (should be root(0)/' . (!empty($process_info['group']) ? $process_info['group'] : '&lt;web server group&gt;') . '(' . (isset($process_info['gid']) ? $process_info['gid'] : '&lt;web server group ID&gt;') . '))';

      if (!($permissions & 04000))
      {
         do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use the set-uid (suid) backend, but $suid_binary does not appear to have the set-uid bit enabled in its permission settings. ' . $config_message, FALSE);
         return TRUE;
      }
      if (!($permissions & 00100))
      {
         do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use the set-uid (suid) backend, but $suid_binary does not appear to be executable by its owner. ' . $config_message, FALSE);
      }
      if (!($permissions & 00010))
      {
         do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use the set-uid (suid) backend, but $suid_binary does not appear to be executable by its group. ' . $config_message, FALSE);
      }
// suid backend is executable by anyone - it itself checks if the web server is calling it
/*
      if ($permissions & 00001)
      {
         do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use the set-uid (suid) backend, but $suid_binary seems to be over-permissioned, where any user is allowed to execute it. ' . $config_message, FALSE);
      }
*/


      // suid file should be owned by root
      //
      if ($file_owner_number !== FALSE
       && $file_owner_number != 0)
      {
         do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use the set-uid (suid) backend, but $suid_binary is not owned by the root user. ' . $config_message, FALSE);
         return TRUE;
      }


// suid backend is executable by anyone - it itself checks if the web server is calling it
/*
      // try to compare web user and file owner
      //
      if ($process_info
       && $file_owner_number !== FALSE
       && $file_group_number !== FALSE)
      {
         if ($process_info['gid'] != $file_group_number
          && $process_info['uid'] != $file_owner_number)
         {
            do_err('Local User Autoresponder and Mail Forwarder plugin is configured to use the set-uid (suid) backend, but $suid_binary does not appear to be executable by the web server, which is running as ' . $process_info['name'] . '(' . $process_info['uid'] . ')/' . $process_info['group'] . '(' . $process_info['gid'] . '). ' . $config_message, FALSE);
            return TRUE;
         }
      }
*/
   }


   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically
   //
   if (!check_sm_version(1, 5, 2))
   {

      // try to find Compatibility, and then that it is v2.0.14+
      //
      if (function_exists('check_plugin_version')
       && check_plugin_version('compatibility', 2, 0, 14, TRUE))
         return FALSE;


      // something went wrong
      //
      do_err('Local User Autoresponder and Mail Forwarder plugin requires the Compatibility plugin version 2.0.14+', FALSE);
      return TRUE;

   }

   return FALSE;

}



