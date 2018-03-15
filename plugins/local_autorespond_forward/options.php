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



// set up SquirrelMail environment
//
if (file_exists('../../include/init.php')) 
   include_once('../../include/init.php');
else if (file_exists('../../include/validate.php')) 
{
   define('SM_PATH', '../../');
   include_once(SM_PATH . 'include/validate.php');
} 
else 
{
// not compatible with SM version less than 1.4.0
die('Sorry, Local User Autoresponder and Mail Forwarder is not compatible with SquirrelMail versions less than 1.4.0');
   chdir('..');
   define('SM_PATH', '../');
   include_once(SM_PATH . 'src/validate.php');
}


// Make sure plugin is activated!
//
global $plugins;
if (!in_array('local_autorespond_forward', $plugins))
   exit;


define('ERR_OK', 0);
define('ERR_NOTFOUND', 1);
define('ERR_NOTSUPPORTED', 46);


include_once(SM_PATH . 'plugins/local_autorespond_forward/functions.php');
if (!local_autorespond_forward_init())
   die('Local User Autoresponder and Mail Forwarder plugin is missing its main configuration file');


global $vacation_delete, $vacation_file, $sq_vacation_file, 
       $sq_forward_addresses_file, $laf_prefs_file,
       $sq_vacation_subject_file, 
       $username, $vacation_path, $data_dir, $domain,
       $forward_file, $color, $set_hostname, $FTP, 
       $local_delivery_syntax, $vacation_command_quotes,
       $only_localpart_in_forward_file, $initialize_when_create,
       $initialize_when_change, $vl_errors, $vl_messages,
       $aliases_full_email_format, $maintain_autoresponder,
       $other_forward_file_contents_prefix, $allow_black_hole,
       $other_forward_file_contents_suffix, $auto_enable_autoresponder,
       $other_forward_file_contents_deleted, $maintain_forwarding,
       $forward_file_format_pattern, $forward_file_format_replace,
       $vacation_subject_default, $vacation_message_default;


if (!$maintain_autoresponder && !$maintain_forwarding)
   die('Local User Autoresponder and Mail Forwarder plugin cannot have both $maintain_autoresponder and $maintain_forwarding turned off');
if (empty($forward_file) && $maintain_forwarding)
   die('Local User Autoresponder and Mail Forwarder plugin cannot have $maintain_forwarding turned on without also having $forward_file configured');



$FTP = FALSE;
$do_autoreply = 0;
$vacation_subject = '';
$vacation_message = '';
$do_forward = 0;
$no_local_delivery = 0;
$forward_addresses = '';
$identity = 0;
$vl_errors = array();
$vl_messages = array();


sq_change_text_domain('local_autorespond_forward');


// get plugin prefs
//
// usually contains these values (PHP variables of the 
// same name will be initialized here):
//
//    do_autoreply
//    do_forward
//    no_local_delivery
//    identity
//
//
if (do_action('list', $laf_prefs_file)) 
{

   $vac_prefs = download_data($laf_prefs_file);

   $vac_prefs = explode("\n", $vac_prefs);

   foreach ($vac_prefs as $a_pref) 
   {
      if (!empty($a_pref) && strpos($a_pref, '#') !== 0 && strpos($a_pref, '=') > 0) 
      {
         list($pref_name, $pref_value) = explode('=', $a_pref, 2);
         $$pref_name = $pref_value;
      }
   }

}



// get vacation text and forwarding addresses
//
if (do_action('list', $sq_vacation_subject_file))
   $vacation_subject = download_data($sq_vacation_subject_file);
if (do_action('list', $sq_vacation_file))
   $vacation_message = download_data($sq_vacation_file);
if (do_action('list', $sq_forward_addresses_file))
   $forward_addresses = download_data($sq_forward_addresses_file);
$old_forward_addresses = $forward_addresses;



// if user has changed settings, override all values
//
if (!sqgetGlobalVar('vac_action', $vac_action, SQ_FORM)) $vac_action = '';
if ($vac_action == 'CHANGE_VACATION_SETTINGS')
{

   // sanitize forwarding addresses
   //
   if ($maintain_forwarding)
   {
      sqgetGlobalVar('forward_addresses', $temp_forward_addresses, SQ_FORM);
      if (preg_match("/[^a-zA-Z0-9_+.@\r\n-]/", $temp_forward_addresses))
         $vl_errors[] = _("Bad characters in list of forwarding addresses");

      else
      {
         $forward_addresses = trim($temp_forward_addresses);
         upload_data($forward_addresses, $sq_forward_addresses_file);
      }
   }


   // checkboxes aren't included when not checked
   //
   if (!$maintain_autoresponder 
    || !sqgetGlobalVar('do_autoreply', $new_do_autoreply, SQ_FORM))
      $new_do_autoreply = 0;


   if (!sqgetGlobalVar('do_forward', $new_do_forward, SQ_FORM))
      $new_do_forward = 0;

   // if no forwarding addresses, turn off forwarding
   //
   if (empty($forward_addresses))
   {
      if (!$do_forward && $new_do_forward)
         $vl_messages[] = _("Forwarding has not been activated (no addresses given)");
      $new_do_forward = 0;
   }

   // if forwarding addresses were previously empty, and new ones
   // are given now, turn on autoresponder automatically
   //
   else if (empty($old_forward_addresses))
      $new_do_forward = 1;

   // notify user when forwarding is enabled/disabled
   // 
   if (!$do_forward && $new_do_forward)
      $vl_messages[] = _("Forwarding has been activated");
   if ($do_forward && !$new_do_forward)
      $vl_messages[] = _("Forwarding has been deactivated");
   $do_forward = $new_do_forward;


   if (!sqgetGlobalVar('no_local_delivery', $no_local_delivery, SQ_FORM))
      $no_local_delivery = 0;

   // NOTE!  As of version 3.0 of this plugin, the meaning
   // of the checkbox for this functionality is inverted,
   // so we have to invert its value here
   //
   if ($no_local_delivery)
      $no_local_delivery = 0;
   else
      $no_local_delivery = 1;



   sqgetGlobalVar('identity', $identity, SQ_FORM);
   if (!$maintain_autoresponder || empty($identity)) 
      $identity = 0;



   // get submitted message body
   //
   if (!$maintain_autoresponder
    || !sqgetGlobalVar('vacation_message', $new_vacation_message, SQ_FORM))
      $new_vacation_message = '';

   // sanitize/standardize newlines
   //
   if (!empty($new_vacation_message))
   {
      $vac_msg = preg_split('/(\r\n|\r|\n)/', $new_vacation_message);
      $new_vacation_message = '';
      foreach ($vac_msg as $msg)
         $new_vacation_message .= trim($msg) . "\n";
      $new_vacation_message = substr($new_vacation_message, 0, -1); // remove last newline
   }

   if (trim($new_vacation_message) == '') 
      $new_vacation_message = '';



   // get submitted message subject
   //
   if (!$maintain_autoresponder
    || !sqgetGlobalVar('vacation_subject', $new_vacation_subject, SQ_FORM))
      $new_vacation_subject = '';

   if (trim($new_vacation_subject) == '') 
      $new_vacation_subject = '';



   // if both subject and message are empty (or only message when
   // configured as such), turn off autoresponder
   //
   if ($auto_enable_autoresponder && $new_do_autoreply
    && (empty($new_vacation_message) 
     && (empty($new_vacation_subject) || $auto_enable_autoresponder == 1)))
   {
      if ($do_autoreply)
         $vl_messages[] = _("Autoresponder has been deactivated");
      else
         $vl_messages[] = _("Autoresponder has not been activated (message is empty)");
      $new_do_autoreply = 0;
   }

   // if new subject and messages (or only message when
   // configured as such) are given (previously empty),
   // turn on autoresponder
   //
   else if ($auto_enable_autoresponder && (!empty($new_vacation_message) 
    && (!empty($new_vacation_subject) || $auto_enable_autoresponder == 1)
    && empty($vacation_message) 
    && (empty($vacation_subject) || $auto_enable_autoresponder == 1)))
   {
      if (!$new_do_autoreply || !$do_autoreply)
         $vl_messages[] = _("Autoresponder has been activated");
      $new_do_autoreply = 1;
   }

   // otherwise, make regular notification when autoresponder is
   // turned on/off
   //
   else if (!$do_autoreply && $new_do_autoreply)
      $vl_messages[] = _("Autoresponder has been activated");
   else if ($do_autoreply && !$new_do_autoreply)
      $vl_messages[] = _("Autoresponder has been deactivated");
   


   // re-initialize vacation database if needed
   //
   if ($new_do_autoreply 
    && (($initialize_when_create && !$do_autoreply) 
     || ($initialize_when_change && ($new_vacation_message != $vacation_message
                                  || $new_vacation_subject != $vacation_subject))))
   {
      if (do_action('init'))
      {
         if (!$do_autoreply)
            $vl_messages[] = _("Autoresponder has been initialized");
         else
            $vl_messages[] = _("Autoresponder has been re-initialized");
      }
   }



   $vacation_message = $new_vacation_message;
   $vacation_subject = $new_vacation_subject;
   $do_autoreply = $new_do_autoreply;



   // depending on $allow_black_hole, turn $no_local_delivery off if 
   // no forwarding addresses are given (or are not in use)
   //
   if (!$maintain_forwarding && !($maintain_autoresponder && $allow_black_hole))
      $no_local_delivery = 0;
   else if ((!$allow_black_hole || ($allow_black_hole == 1 && !$maintain_autoresponder)) 
    && (!$do_forward || empty($forward_addresses)))
   {
      if ($no_local_delivery)
         $vl_messages[] = _("Messages will be saved in this account (no forwarding is active)");
      $no_local_delivery = 0;
   }
   else if ($allow_black_hole == 1 && (!$do_forward || empty($forward_addresses)) && !$do_autoreply)
   {
      if ($no_local_delivery)
         $vl_messages[] = _("Messages will be saved in this account (no forwarding or auto-reply is active)");
      $no_local_delivery = 0;
   }



   // now upload all this
   //
   upload_data($vacation_subject, $sq_vacation_subject_file);
   upload_data($vacation_message, $sq_vacation_file);
   $vac_pref_array = array('do_autoreply' => $do_autoreply, 
                           'do_forward' => $do_forward,
                           'no_local_delivery' => $no_local_delivery,
                           'identity' => $identity);
   $vac_prefs = "# DO NOT EDIT THIS FILE BY HAND\n";
   $vac_prefs .= implode_with_keys("\n", $vac_pref_array);
   upload_data($vac_prefs, $laf_prefs_file);


}


// if user comes to this settings page fresh, and all settings
// are blank, fill in with defaults (if provided)
//
else if ($maintain_autoresponder 
 // not necessary:
 // && (!empty($vacation_subject_default) || !empty($vacation_message_default))
 && empty($do_autoreply)
 && empty($vacation_subject)
 && empty($vacation_message)
 && empty($do_forward)
 && empty($no_local_delivery)
 && empty($forward_addresses)
 && empty($identity))
{
   $vacation_subject = $vacation_subject_default;
   $vacation_message = $vacation_message_default;
}



// allow vacation executable to handle aliases; all
// aliases have to be defined in SM prefs already
//
$alias_list = '';
$ident = getPref($data_dir, $username, 'identities');
if (!$ident) $ident = 1;
for ($i = 0; $i < $ident; $i++) 
{

   if ($i == 0) 
      $prefs_email = getPref($data_dir, $username, 'email_address');
   else 
      $prefs_email = getPref($data_dir, $username, 'email_address' . $i);

   if ($aliases_full_email_format)
      $alias = array('', $prefs_email);
   else
      preg_match('/(^[-_.[:alnum:]]+)/', $prefs_email, $alias);

   if (isset($alias[1]) && $alias[1] != $username)
      $alias_list .= '-a ' . $alias[1] . ' ';

}
 


// build list of possible FROM headers
//
$full_name = getPref($data_dir, $username, 'full_name');
$email_addr = getPref($data_dir, $username, 'email_address');
$dom = substr($email_addr, strpos($email_addr, '@') + 1);
if (empty($email_addr))
{
   if (strpos($username, '@') === FALSE)
      $email_addr = $username . '@' . $domain;
   else
      $email_addr = $username;
   $dom = $domain;
}
$formatted_address = $full_name;
if (!empty($email_addr))
{
   if (!empty($full_name))
      $formatted_address .= " <$email_addr>";
   else
      $formatted_address = "$email_addr";
}
$idents = array($formatted_address);
for ($i = 1; $i < $ident; $i ++) 
{
   $full_name = getPref($data_dir, $username, 'full_name' . $i);
   $email_addr = getPref($data_dir, $username, 'email_address' . $i);
   $formatted_address = $full_name;
   if (!empty($email_addr))
   {
      if (!empty($full_name))
         $formatted_address .= " <$email_addr>";
      else
         $formatted_address = "$email_addr";
   }
   $idents[] = $formatted_address;
}
if (empty($idents[$identity])) $identity = 0;



// build the auto-reply message file
//
$vacation_header = 'From: ' . $idents[$identity];

$vacation_header .= "\n" . 'Subject: ' . $vacation_subject . "\n\n";



// upload message (only if Submit button was clicked)
//
if ($vac_action == 'CHANGE_VACATION_SETTINGS')
   upload_data($vacation_header . $vacation_message . "\n", $vacation_file);



// build the .forward file (only if Submit button was clicked)
//
if (!empty($forward_file) && $vac_action == 'CHANGE_VACATION_SETTINGS')
{

   $forward_file_contents = '';
   if ($only_localpart_in_forward_file && strpos($username, '@') !== FALSE)
      $vac_username = substr($username, 0, strpos($username, '@'));
   else
      $vac_username = $username;


   // other addresses
   //
   // some systems having problems with line endings... so make sure
   // it's just \n's..
   //
   if ($maintain_forwarding && !empty($forward_addresses))
   {
      $forward_addrs = preg_split('/\s/', $forward_addresses);
      $forward_addresses = '';
      foreach ($forward_addrs as $addr)
         if (!empty($addr))
            $forward_addresses .= trim($addr) . "\n";

      if ($do_forward)
         $forward_file_contents .= $forward_addresses;
   }
   

   // pipe to vacation executable
   //
   if ($do_autoreply)
      $forward_file_contents .= $vacation_command_quotes . '|' . $vacation_path . ' '
                             . ($set_hostname ? '-h ' . $dom . ' ' : '')
                             . $alias_list . $vac_username . $vacation_command_quotes . "\n";


   // if we have no other .forward contents to here,
   // we don't need a .forward file... delete it
   //
   if (empty($forward_file_contents))
   {

      // first check if there are other, external forward file 
      // contents, because if so, we won't want to delete it
      //
      if (!empty($other_forward_file_contents_deleted))
      {
         $forward_file_contents = str_replace('###USERNAME###',
                                              $vac_username,
                                              implode("\n", 
                                                      $other_forward_file_contents_deleted)
                                             );
      }

      // now we can check if we actually want to delete 
      // the file or save it 
      //
      if (empty($forward_file_contents))
         do_action('delete', $forward_file);
      else
         upload_data($forward_file_contents, $forward_file);

   }


   // otherwise finsih off the .forward file and upload it
   //
   else 
   {

      // local delivery (skip if user turned it off)
      //
      if (!$no_local_delivery)
         $forward_file_contents .= str_replace('###USERNAME###', $vac_username, $local_delivery_syntax) . "\n";


      // manage external content
      //
      if (!empty($other_forward_file_contents_prefix))
      {
         $forward_file_contents = str_replace('###USERNAME###',
                                              $vac_username,
                                              implode("\n", 
                                                      $other_forward_file_contents_prefix)
                                             )
                                . "\n" . $forward_file_contents;
      }
      if (!empty($other_forward_file_contents_suffix))
      {
         $forward_file_contents .= "\n" 
                                . str_replace('###USERNAME###',
                                              $vac_username,
                                              implode("\n", 
                                                      $other_forward_file_contents_suffix)
                                             );
      }


      // final chance to fiddle with .forward contents
      //
      if (!empty($forward_file_format_pattern) && !empty($forward_file_format_replace))
         $forward_file_contents = preg_replace($forward_file_format_pattern,
                                               $forward_file_format_replace,
                                               $forward_file_contents);

      // upload
      //
      upload_data(trim($forward_file_contents) . "\n", $forward_file);

   }

}



// need to wipe out the vacation message file when user clears out 
// the message and subject text or turns off the autoresponder
// (only if Submit button was clicked)
//
if ((!$do_autoreply || (empty($vacation_message) && empty($vacation_subject)))
 && $vac_action == 'CHANGE_VACATION_SETTINGS')
{
   do_action('delete', $vacation_delete . ',' . $vacation_file);
}



// log out of FTP connection when done with everything
//
global $laf_backend;
if (strtolower($laf_backend) == 'ftp' && is_resource($FTP)) 
   ftp_quit($FTP);



// -----------------------------------------------------------------------
//                              INTERFACE
// -----------------------------------------------------------------------



sq_change_text_domain('squirrelmail');
displayPageHeader($color, '');
sq_change_text_domain('local_autorespond_forward');



// NOTE!  As of version 3.0 of this plugin, the meaning
// of the checkbox for this functionality is inverted,
// so we have to invert its value here
//
$no_local_delivery = !$no_local_delivery;



if (check_sm_version(1, 5, 2))
{
   global $oTemplate;
   $oTemplate->assign('color', $color);
   $oTemplate->assign('vac_action', $vac_action);
   $oTemplate->assign('do_autoreply', $do_autoreply);
   $oTemplate->assign('ident', $ident);
// Note some of these rely on being auto-sanitized (htmlspecialchars) when assigned to template; as of the release date of v3.0 of this plugin, that functionality has yet to be added to the 1.5.2 core
   $oTemplate->assign('idents', $idents);
   $oTemplate->assign('identity', $identity);
   $oTemplate->assign('vacation_subject', $vacation_subject);
   $oTemplate->assign('vacation_message', $vacation_message);
   $oTemplate->assign('maintain_autoresponder', $maintain_autoresponder);
   $oTemplate->assign('maintain_forwarding', $maintain_forwarding);
   $oTemplate->assign('do_forward', $do_forward);
   $oTemplate->assign('no_local_delivery', $no_local_delivery);
   $oTemplate->assign('forward_addresses', $forward_addresses);
   $oTemplate->assign('allow_black_hole', $allow_black_hole);
   $oTemplate->assign('vl_errors', $vl_errors);
   $oTemplate->assign('vl_messages', $vl_messages);
   $oTemplate->display('plugins/local_autorespond_forward/options.tpl');
   $oTemplate->display('footer.tpl');
}
else
{

   // we can still use the template file - just trick
   // the one from the default template set
   // 
   global $t;
   $t = array(); // no need to put config vars herein, they are already globalized
   $sanitized_idents = array();
   foreach ($idents as $i => $formatted_address)
      $sanitized_idents[$i] = htmlspecialchars($formatted_address);
   $idents = $sanitized_idents;
   $vacation_subject = sq_htmlspecialchars($vacation_subject);
   $vacation_message = sq_htmlspecialchars($vacation_message);
   $forward_addresses = sq_htmlspecialchars($forward_addresses);

   include_once(SM_PATH . 'plugins/local_autorespond_forward/templates/default/options.tpl');
   echo '</body></html>';

}

sq_change_text_domain('squirrelmail');



// -----------------------------------------------------------------------
//                              FUNCTIONS
// -----------------------------------------------------------------------



/**
  * Do Action
  *
  * Serves as a proxy between PHP code and the binary 
  * that can access the local file system.
  *
  * Can connect to server by either FTP or SUID script.
  *
  * @param string  $action This should be one of five values
  *                        that indicate what action is to be
  *                        taken: 
  *                          'list'   determines if a file exists
  *                          'put'    uploads the given file 
  *                          'get'    downloads the given file
  *                          'delete' removes the indicated file
  *                          'init'   initializes autoresponder
  * @param string  $remoteFile The name of the file to be manipulated
  *                            on the local file system.
  * @param string  $localFile The name of the file to be uploaded or
  *                           downloaded (basically, this is usually
  *                           just a temporary file).
  * @param boolean $list_err  When TRUE, for the "list" command, a
  *                           file not found error will generate 
  *                           error output to the user (OPTIONAL;
  *                           default is FALSE - don't show file not
  *                           found errors)
  *
  * @return boolean FALSE if the action failed, TRUE otherwise
  *
  */
function do_action($action, $remoteFile='', $localFile='', $list_err=FALSE) 
{

   global $username, $key, $onetimepad, $ftp_server, $laf_ldap_attribute,
          $suid_binary, $vac_debug, $laf_backend, $laf_ftp_mode,
          $ftp_passive, $ldap_lookup_ftp_server, $laf_ldap_base, $laf_ldap_server,
          $use_ssl_ftp, $FTP, $www_initialize, $vl_errors, 
          $auth_user_localpart_only, $debug_suid_output_file;


   $laf_backend = strtolower($laf_backend);


   if ($auth_user_localpart_only && strpos($username, '@') !== FALSE)
      $auth_username = substr($username, 0, strpos($username, '@'));
   else
      $auth_username = $username;


   // set up debugging output for suid backend if needed
   //
   if ($vac_debug && !empty($debug_suid_output_file))
      $suid_debug = ' 2>>' . $debug_suid_output_file . ' ';
   else
      $suid_debug = '';


   // decrypt password
   //
   sqgetGlobalVar('key', $key, SQ_COOKIE);
   sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);
   $password = OneTimePadDecrypt($key, $onetimepad); 


   // look up ftp server in LDAP if necessary
   //
   if ($ldap_lookup_ftp_server)
   {

      // try to get ftp server name from user's session if possible
      //
      sqgetGlobalVar('vac_ftp_server', $vac_ftp_server, SQ_SESSION);


      // do the LDAP lookup
      //
      if (empty($vac_ftp_server))
      {

         $LDAP = ldap_connect($laf_ldap_server);
         if ($LDAP) 
         {

            $r = ldap_bind($LDAP);    // this is an "anonymous" bind, ty


            if ($r)
            {
               $sr = ldap_search($LDAP, $laf_ldap_base, "uid=$auth_username");
               // Maybe check for search error... 
            }
            else
            {
               $vl_errors[] = _("Error: Could not bind to LDAP server");
               return FALSE;
            }


            $entry = ldap_first_entry($LDAP, $sr);
            $values = ldap_get_values($LDAP, $entry, $laf_ldap_attribute);
            $vac_ftp_server = $values[0];


            // check for null return from ldap
            //
            if (!isset($vac_ftp_server)) 
            {
               $vl_errors[] = sprintf(_("Error: Could not find FTP server for %s in LDAP server"), $auth_username);
               ldap_close($LDAP);
               return FALSE;
            }
            else 
               sqsession_register($vac_ftp_server, 'vac_ftp_server');


            ldap_close($LDAP);

         } 
         else 
         {
            $vl_errors[] = _("Error: Could not connect to LDAP server");
            return FALSE;
         }

      }

      $ftp_server = $vac_ftp_server;

   }


   // look for possible $ftp_server overrides from vlogin so that
   // $ftp_server can be different per domain, per user, etc.
   // 
   global $vlogin_local_autorespond_forward_ftp_server;
   if (!empty($vlogin_local_autorespond_forward_ftp_server))
      $ftp_server = $vlogin_local_autorespond_forward_ftp_server;


   $server = 'localhost';

   $result = FALSE;

   if ($vac_debug)
   {
      echo "ACTION: $action === REMOTE: $remoteFile === LOCAL: $localFile === USER: $auth_username === PASSWORD: $password<br />";
   }

   if ($laf_backend == 'ftp')
   {

      // set transfer mode
      //
      if ($laf_ftp_mode)
         $ftp_mode = FTP_BINARY;
      else
         $ftp_mode = FTP_ASCII;


      // use FTP over SSL?
      //
      if ($use_ssl_ftp) $ftp_conn_func = 'ftp_ssl_connect';
      else $ftp_conn_func = 'ftp_connect';


      // only connect to FTP server once per page request
      //
      if (!$FTP)
      {

         $FTP = $ftp_conn_func($ftp_server);


         if (!$FTP)
         {
            $vl_errors[] = _("Error: Unable to connect to FTP server. Please try again later");
            return FALSE;
         }
         if (!($FTPConn = ftp_login($FTP, $auth_username, $password)))
         {
            $vl_errors[] = _("Error: Unable to log in to FTP server. Please contact your system administrator");
            ftp_quit($FTP);
            return FALSE;
         }
         if ($ftp_passive && !ftp_pasv($FTP, TRUE))
         {
            $vl_errors[] = _("Error: Unable to switch to passive FTP mode. Please contact your system administrator");
            ftp_quit($FTP);
            return FALSE;
         }

         // TODO: this flushes browser output too, and might create problems...?
         flush();

      }

   }

   switch ($action) 
   {

      // check whether file exists
      //
      case 'list': 

         if ($laf_backend == 'ftp')
         {
            $ftpList = ftp_size($FTP, $remoteFile);

            if ($ftpList > 0) 
               $result = TRUE;
            else 
               $result = FALSE;
         }
         else // suid
         {
	    $cmd = $suid_binary . ' ' . escapeshellarg($server) . ' ' 
                 . escapeshellarg($auth_username) . ' ' . " 'list' " 
                 . escapeshellarg($remoteFile) . $suid_debug;

            if (!($SUID = popen($cmd, 'w')))
            {
               $vl_errors[] = _("Error: Unable to establish connection. Please contact your system administrator");
               $result = FALSE;
            }
            else if (fwrite($SUID, $password . "\n") === FALSE)
            {
               $vl_errors[] = _("Error: Unable to send password. Please contact your system administrator");
               $result = FALSE;
            }
            else 
            {
               $status = vl_pclose($SUID);
 
               if ($status != ERR_OK)
               {

                  // we want to silently ignore file not 
                  // found errors unless told not to
                  //
                  if ($status != ERR_NOTFOUND || $list_err)
                     $vl_errors[] = sprintf(_("Error: Unable to handle list request (%s)"), $status);
                  $result = FALSE;
               }

               else $result = TRUE;
            }
         }

         break;



      // upload $remoteFile to $localFile
      //
      case 'put': 

         if ($laf_backend == 'ftp')
         {
            if (ftp_put($FTP, $remoteFile, $localFile, $ftp_mode))
               $result = TRUE;
            else 
            {
               $vl_errors[] = _("Error: Unable to upload file. Please contact your system administrator");
               $result = FALSE;
            }
         }
         else // suid
         {
            $cmd = $suid_binary . ' ' . escapeshellarg($server) . ' '
                 . escapeshellarg($auth_username) . ' ' . " 'put' "
                 . escapeshellarg($localFile) . ' ' . escapeshellarg($remoteFile)
                 . $suid_debug;

            if (!($SUID = popen($cmd, 'w')))
            {
               $vl_errors[] = _("Error: Unable to establish connection. Please contact your system administrator");
               $result = FALSE;
            }
            else if (fwrite($SUID, $password . "\n") === FALSE)
            {
               $vl_errors[] = _("Error: Unable to send password. Please contact your system administrator");
               $result = FALSE;
            }
            else
            {
               $status = vl_pclose($SUID);

               if ($status != ERR_OK)
               {
                  $vl_errors[] = sprintf(_("Error: Unable to handle upload request (%s)"), $status);
                  $result = FALSE;
               }

               else $result = TRUE;
            }
         }

         break;



      // download $remoteFile to $localFile
      // 
      case 'get': 

         if ($laf_backend == 'ftp')
         {
            if (ftp_get($FTP, $localFile, $remoteFile, $ftp_mode))
               $result = TRUE;
            else 
            {
               $vl_errors[] = _("Error: unable to download file. Please contact your system administrator");
               $result = FALSE;
            }
         }
         else // suid
         {
            $cmd = $suid_binary . ' ' . escapeshellarg($server) . ' '
                 . escapeshellarg($auth_username) . ' ' . " 'get' "
                 . escapeshellarg($remoteFile) . ' ' . escapeshellarg($localFile)
                 . $suid_debug;

            if (!($SUID = popen($cmd, 'w')))
            {
               $vl_errors[] = _("Error: Unable to establish connection. Please contact your system administrator");
               $result = FALSE;
            }
            else if (fwrite($SUID, $password . "\n") === FALSE)
            {
               $vl_errors[] = _("Error: Unable to send password. Please contact your system administrator");
               $result = FALSE;
            }
            else
            {
               $status = vl_pclose($SUID);

               if ($status != ERR_OK)
               {
                  $vl_errors[] = sprintf(_("Error: Unable to handle download request (%s)"), $status);
                  $result = FALSE;
               }

               else $result = TRUE;
            }
         }

         break;



      // delete files listed in $remoteFile if they exist
      //
      case 'delete': 

         $remoteFiles = explode(',', $remoteFile);
         $result = TRUE;
         foreach ($remoteFiles as $file)
         {

            if ($laf_backend == 'ftp')
            {
               $ftpList = ftp_size($FTP, $file);
               if ($ftpList > 0 && !ftp_delete($FTP, $file))
               {
                  $vl_errors[] = _("Error: unable to delete file. Please contact your system administrator");
                  $result = FALSE;
               }
            }
            else // suid
            {
	       $cmd = $suid_binary . ' ' . escapeshellarg($server) . ' ' 
                    . escapeshellarg($auth_username) . ' ' . " 'list' " 
                    . escapeshellarg($file) . $suid_debug;

               if (!($SUID = popen($cmd, 'w')))
               {
                  $vl_errors[] = _("Error: Unable to establish connection. Please contact your system administrator");
                  $result = FALSE;
               }
               else if (fwrite($SUID, $password . "\n") === FALSE)
               {
                  $vl_errors[] = _("Error: Unable to send password. Please contact your system administrator");
                  $result = FALSE;
               }
               else 
               {
                  $status = vl_pclose($SUID);
 
                  if ($status != ERR_OK)
                  {
                     // we want to silently ignore file not found errors
                     //
                     if ($status != ERR_NOTFOUND)
                        $vl_errors[] = sprintf(_("Error: Unable to handle list request (%s)"), $status);
                     $result = FALSE;
                  }
                  else
                  {
                     $cmd = $suid_binary . ' ' . escapeshellarg($server) . ' '
                          . escapeshellarg($auth_username) . ' ' . " 'delete' "
                          . escapeshellarg($file) . $suid_debug;

                     if (!($SUID = popen($cmd, 'w')))
                     {
                        $vl_errors[] = _("Error: Unable to establish connection. Please contact your system administrator");
                        $result = FALSE;
                     }
                     else if (fwrite($SUID, $password . "\n") === FALSE)
                     {
                        $vl_errors[] = _("Error: Unable to send password. Please contact your system administrator");
                        $result = FALSE;
                     }
                     else
                     {
                        $status = vl_pclose($SUID);

                        if ($status != ERR_OK)
                        {
                           $vl_errors[] = sprintf(_("Error: Unable to handle delete request (%s)"), $status);
                           $result = FALSE;
                        }

                        else $result = TRUE;
                     }
                  }
               }
            }

         }

         break;



      // initialize vacation autoresponder
      //
      case 'init':

         if ($laf_backend == 'suid')
         {     
            $cmd = $suid_binary . ' ' . escapeshellarg($server) . ' '
                 . escapeshellarg($auth_username) . ' ' . " 'init' " . $suid_debug;
                  
            if (!($SUID = popen($cmd, 'w')))
            {  
               $vl_errors[] = _("Error: Unable to establish connection. Please contact your system administrator");
               $result = FALSE;
            }
            else if (fwrite($SUID, $password . "\n") === FALSE)
            {
               $vl_errors[] = _("Error: Unable to send password. Please contact your system administrator");
               $result = FALSE;
            }
            else
            {
               $status = vl_pclose($SUID);
         
               if ($status != ERR_OK)
               { 
                  if ($status == ERR_NOTSUPPORTED) 
                     $vl_errors[] = sprintf(_("Error: Unable to handle init request (%s); you might need to configure with --enable-vacation"), $status);
                  else
                     $vl_errors[] = sprintf(_("Error: Unable to handle init request (%s)"), $status);
                  $result = FALSE;
               }
            
               else $result = TRUE;
            }  
         }    
         else  // ftp
         {

            $init = exec(str_replace('###USERNAME###', $auth_username, $www_initialize), $output, $retval);

            if ($retval)
            {
               if (!isset($output[0])) $output[0] = '';
               $vl_errors[] = sprintf(_("An error occurred initializing the autoresponder: %s"), $output[0]);
               $result = FALSE;
            }

         }

         break;



      // unknown action
      //
      default:

         $vl_errors[] = sprintf(_("Unknown action: %s"), $action);
         $result = FALSE;

   }

   // keep FTP connection open for duration of whole page request
   //if ($laf_backend == 'ftp') ftp_quit($FTP);

   return $result;

}



/**
  * Download Data
  *
  * Download remote file and return its contents.
  *
  * @param string $remoteFile The path and name of the 
  *                           file to be retrieved.
  *
  * @return string The contents of the desired file are returned
  *
  */
function download_data($remoteFile) 
{

   global $attachment_dir, $username, $vl_errors;

   $tempFile = realpath($attachment_dir) . '/' . $username . '.mailcfg.tmp';
   
   $result = '';
   
   $get = do_action('get', $remoteFile, $tempFile);
   
   if ($get) 
   {
      if (($FILE = fopen($tempFile, 'r')) === FALSE)
      {
         $vl_errors[] = _("An error occurred attempting to read temp file; check permissions on attachments directory");
         @fclose($FILE);
         @unlink($tempFile);  
         return '';
      }

      while (!feof($FILE)) 
         $result .= fread($FILE, 1024);

      fclose($FILE);
      unlink($tempFile);
   }
   
   return $result;

}



/**
  * Upload Data
  *
  * Upload string to a remote file.
  *
  * @param string $data The actual data to be uploaded
  *                     to the indicated file.
  * @param string $remoteFile The path and name of the 
  *                           file to be uploaded.
  *
  * @return boolean FALSE if the upload failed, TRUE otherwise
  *
  */
function upload_data($data, $remoteFile) 
{

   global $attachment_dir, $username, $vac_umask, $vl_errors;
   
   $tempFile = realpath($attachment_dir) . '/' . $username . '.mailcfg.tmp';
   
   umask($vac_umask);

   if (($FILE = fopen($tempFile, 'w')) === FALSE)
   {
      $vl_errors[] = _("An error occurred attempting to create temp file; check permissions on attachments directory");
      @fclose($FILE);
      @unlink($tempFile);  
      return FALSE;
   }

   fwrite($FILE, stripslashes($data));
   fclose($FILE);
  
   $put = do_action('put', $remoteFile, $tempFile);

   // delete localFile
   //
   unlink($tempFile);  
   
   return $put;

}



/**
  * Convenience function for storing user prefs in file
  *
  * @param string $glue Delimiter that will separate key/value pairs 
  *                     (keys and values are always separated from
  *                     each other by an equal sign)
  * @param string $array The associative array to disassemble
  *
  * @return string The flattened array, ready for storage in a file
  *
  */
function implode_with_keys($glue, $array) 
{

   $output = array();

   foreach ($array as $key => $value)
      $output[] = $key . '=' . $value;

   return implode($glue, $output);

}



/**
  * Wrapper for pclose() that deals intelligently
  * with return status of the child process.
  *
  * @param resource $PROC Handle on the child process being closed
  * 
  * @return int The return code of the child process or -1 if the 
  *             child was (not normally) terminated.
  *
  */
function vl_pclose($PROC) 
{

   $status = pclose($PROC);


   // we prefer to use the process control functions 
   // to do this right, but if they are not enabled...
   //
   if (!function_exists('pcntl_wexitstatus'))
   {
      if ($status > 256) $status /= 256;
      return $status;
   }


   // check for normal exit condition, and if found,
   // get exit status in portable manner
   //
   if (!pcntl_wifexited($status))
      return -1;
   else
      return pcntl_wexitstatus($status);

}



