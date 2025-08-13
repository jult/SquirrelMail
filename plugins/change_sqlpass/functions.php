<?php

/**
  * SquirrelMail Change SQL Password Plugin
  * Copyright (C) 2001-2002 Tyler Akins
  *               2002 Thijs Kinkhorst <kink@users.sourceforge.net>
  *               2002-2005 Paul Lesneiwski <paul@openguild.net>
  * This program is licensed under GPL. See COPYING for details
  *
  * @package plugins
  * @subpackage Change SQL Password
  *
  */


if (!defined('PASSWORD_ENCRYPTION_NONE'))
   define('PASSWORD_ENCRYPTION_NONE', 'NONE');
if (!defined('PASSWORD_ENCRYPTION_MYSQL_PASSWORD'))
   define('PASSWORD_ENCRYPTION_MYSQL_PASSWORD', 'MYSQLPWD');
if (!defined('PASSWORD_ENCRYPTION_MYSQL_ENCRYPT'))
   define('PASSWORD_ENCRYPTION_MYSQL_ENCRYPT', 'MYSQLENCRYPT');
if (!defined('PASSWORD_ENCRYPTION_PHPCRYPT'))
   define('PASSWORD_ENCRYPTION_PHPCRYPT', 'PHPCRYPT');
if (!defined('PASSWORD_ENCRYPTION_MD5CRYPT'))
   define('PASSWORD_ENCRYPTION_MD5CRYPT', 'MD5CRYPT');
if (!defined('PASSWORD_ENCRYPTION_MD5'))
   define('PASSWORD_ENCRYPTION_MD5', 'MD5');



/**
  * Include Pear DB class
  *
  */
function csp_get_pear_db()
{

   global $csp_debug;

   load_config('change_sqlpass', array('config.php'));


   // mask include errors if not in debug mode
   //
   if ($csp_debug)
      $if_statement = 'return !include_once(\'DB.php\');';
   else
      $if_statement = 'return !@include_once(\'DB.php\');';
   
   if (eval($if_statement))
   {
      global $color;
      bindtextdomain('change_sqlpass', SM_PATH . 'locale');
      textdomain('change_sqlpass');
      $text = _("Could not find Pear DB library");
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
      plain_error_message($text, $color);
      exit;
   }

}



/**
  * Get a database connection
  *
  * If a connection has already been opened, return that,
  * otherwise, open a new connection.
  *
  * @return object The database connection handle.
  *
  */
function csp_get_database_connection()
{

   global $csp_db_connection, $csp_dsn;


   load_config('change_sqlpass', array('config.php'));
   csp_get_pear_db();


   // make a new connection if needed; exit if failure
   //
   if (empty($csp_db_connection))
   {

      $csp_db_connection = DB::connect($csp_dsn);
      if (DB::isError($csp_db_connection))
      {
         global $color;
         bindtextdomain('change_sqlpass', SM_PATH . 'locale');
         textdomain('change_sqlpass');
         $text = _("Could not make database connection");
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
         textdomain('squirrelmail');
         plain_error_message($text, $color);
         exit;
      }
      $csp_db_connection->setFetchMode(DB_FETCHMODE_ORDERED);

   }


   // return connection
   //
   return $csp_db_connection;

}



/**
  * Parse current user's username
  *
  * @return array A list, in this order, of the full username
  *               with domain ("user@example.com"), the user
  *               name only ("user"), and the domain only ("example.com")
  *
  */
function csp_parse_username()
{

   global $username, $domain, $csp_delimiter;

   load_config('change_sqlpass', array('config.php'));

   if (strpos($username, $csp_delimiter) !== FALSE)
   {
      list($user, $dom) = explode($csp_delimiter, $username);
      $full_username = $username;
   }
   else
   {
      $user = $username;
      $dom = $domain;
      $full_username = $user . $csp_delimiter . $dom;
   }
   
   return array($full_username, $user, $dom);

}



/**
  * Determines if user has the force password flag turned on
  *
  * @return boolean TRUE if force password flag is on
  *                 for the current user, FALSE otherwise
  *
  */
function check_password_force_flag()
{ 

   global $force_change_password_cache, $force_change_password_check_query, $csp_debug;


   // return cached value if we've already been here
   //
   if (!empty($force_change_password_cache))
      return $force_change_password_cache == 'yes';


   load_config('change_sqlpass', array('config.php'));


   // if not being used, just return FALSE
   //
   if (empty($force_change_password_check_query)) return FALSE;


   // get database connection
   //
   $db = csp_get_database_connection();


   list($full_username, $user, $dom) = csp_parse_username();


   $sql = $force_change_password_check_query;
   $sql = str_replace(array('%1', '%2', '%3'), array($full_username, $user, $dom), $sql);
   $force = $db->getAll($sql);


   // check for database errors
   //
   if (DB::isError($force))
   {
      global $color;
      $msg = $force->getMessage();
      bindtextdomain('change_sqlpass', SM_PATH . 'locale');
      textdomain('change_sqlpass');
      $text = sprintf(_("DATABASE ERROR: could not lookup force change password flag: %s"), ($csp_debug ? $sql . ' ---- ' . $msg : ''));
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
      plain_error_message($text, $color);
      exit;
   }


   // debug if needed
   //
   if ($csp_debug)
      echo sprintf(_("Force change password query result is: %s<br />Query was: %s<br /><br />"), $force[0][0], $sql);


   // get flag value out of array structure
   //
   if ($force[0][0])
   {
      $force_change_password_cache = 'yes';
      sqsession_register($force_change_password_cache, 'csp_was_force_mode');
   }
   else $force_change_password_cache = 'no';
   return $force_change_password_cache == 'yes';

}



/**
  * Forces user into change password screen if needed
  *
  */
function csp_password_force_do()
{
   
   // do we need to hijack the page and force password change?
   //
   if (check_password_force_flag())
   {
   
      // validate and process a password submission
      //
      if (sqgetGlobalVar('csp_submit_change', $csp_submit_change, SQ_FORM))
         $messages = process_password_change_request();


      else
      {
         csp_redirect_to_ssl_connection();
         $messages = array();
      }


      show_change_pwd_screen($messages);
      echo '</body></html>';
      exit;
      
   }  
      
}     



/**
  * Show change password option section on options page
  *
  */
function csp_show_optblock_do()
{

   global $optpage_blocks, $base_uri;
   if (empty($base_uri)) $base_uri = sqm_baseuri();
        
   bindtextdomain('change_sqlpass', SM_PATH . 'locale');
   textdomain('change_sqlpass');

   $optpage_blocks[] = array(
      'name' => _("Change Password"),
      'url' => $base_uri . 'plugins/change_sqlpass/options.php',
      'desc' => _("Use this to change your email password."),
      'js' => FALSE
   );          
                
   bindtextdomain('squirrelmail', SM_PATH . 'locale');
   textdomain('squirrelmail');

}       



/**
  * Shows status message at top of options screen
  * after password was changed
  *
  */
function csp_show_success_do()
{

   global $optpage_name;

        
   if (sqgetGlobalVar('csp_change_success', $csp_change_success, SQ_FORM))
   {

      bindtextdomain('change_sqlpass', SM_PATH . 'locale');
      textdomain('change_sqlpass');

      if ($csp_change_success == 'yes')
         $optpage_name = _("Password changed successfully.<br />Please use your new password to log in from now on.");
      else
         $optpage_name = _("Password change cancelled.<br />Your password has NOT been changed.");
                
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');

   }
        
}       
 
 

/**
  * Check if session is HTTPS originally (for use later)
  *
  */
function csp_check_for_https_do()
{

   global $csp_secure_port;

   load_config('change_sqlpass', array('config.php'));


   // allow vlogin overrides of port settings
   //
   if (!empty($vlogin_csp_secure_port))
      $csp_secure_port = $vlogin_csp_secure_port;


   if (!$csp_secure_port)
      return;


   if (!sqgetGlobalVar('SERVER_PORT', $SERVER_PORT, SQ_SERVER))
      $SERVER_PORT = 0;


   $is_https = ($csp_secure_port == $SERVER_PORT);


   // make note if session was already encrypted in HTTPS
   //
   if ($is_https && !sqgetGlobalVar('csp_was_already_encrypted_port', $csp_was_already_encrypted_port, SQ_SESSION))
      sqsession_register($SERVER_PORT, 'csp_was_already_encrypted_port');

}



/**
  * Redirects to SSL/HTTPS connection when needed
  *
  * NOTE that this function will not return if 
  * the redirection is applied, but it will return
  * if none was needed (not using HTTPS connections
  * for this plugin, or we are already in HTTPS).
  *
  * @param string $target_uri The location to be 
  *                           redirected to (optional,
  *                           default is current request
  *                           address)
  *
  */
function csp_redirect_to_ssl_connection($target_uri='')
{

   global $csp_secure_port;


   load_config('change_sqlpass', array('config.php'));


   // allow vlogin overrides of port settings
   //
   if (!empty($vlogin_csp_secure_port)) 
      $csp_secure_port = $vlogin_csp_secure_port;


   if (empty($target_uri))
      // TODO: I don't think this includes query string, but 
      //       I don't think we will ever need it here(??)
      $target_uri = php_self(); 
   if (strpos($target_uri, '/') !== 0)
      $target_uri = '/' . $target_uri;


   if (!$csp_secure_port) 
      return;


   if (!sqgetGlobalVar('SERVER_PORT', $SERVER_PORT, SQ_SERVER))
      $SERVER_PORT = 0;


   $is_https = ($csp_secure_port == $SERVER_PORT);


   // and redirect if need be
   //
   if (!$is_https)
   {

      sqgetGlobalVar('HTTP_HOST', $HTTP_HOST, SQ_SERVER);

      session_write_close();

      if ($csp_secure_port != 443)
         header('Location: https://' . $HTTP_HOST . ':' . $csp_secure_port . $target_uri);
      else
         header('Location: https://' . $HTTP_HOST . $target_uri);

      echo "\n\n";
      exit;

   }

}



/**
  * Render change password screen
  *
  * NOTE that this function will close out all the main tables
  * and everything that is otherwise started by SquirrelMail
  * page header functions, etc., all except the final closing
  * body and html tags.
  *
  * @param mixed  $messages   A list of any messages to be shown to user
  *                           (or a single message string) (optional; 
  *                           default = no messages)
  *
  */
function show_change_pwd_screen($messages='')
{
   
   global $color, $csp_non_standard_http_port, $base_uri,
          $csp_secure_port;


   load_config('change_sqlpass', array('config.php'));


   // allow vlogin overrides of port settings
   //
   if (!empty($vlogin_csp_non_standard_http_port)) 
      $csp_non_standard_http_port = $vlogin_csp_non_standard_http_port;
   if (!empty($vlogin_csp_secure_port))
      $csp_secure_port = $vlogin_csp_secure_port;


   if (!is_array($messages)) $messages = array($messages);


   $force = check_password_force_flag();
   if ($force)
      array_unshift($messages, _("Periodically, we ask that you change your password.  This is done to help maintain good security for your email account."));


   $message_text = '';
   foreach ($messages as $line)
      if (!empty($line))
         $message_text .= htmlspecialchars($line) . "<br />\n";


   // figure out where the cancel button should send the user
   // if the session was previously HTTPS, make sure it stays as such
   //
   sqgetGlobalVar('csp_was_already_encrypted_port', $csp_was_already_encrypted_port, SQ_SESSION);
   sqgetGlobalVar('HTTP_HOST', $HTTP_HOST, SQ_SERVER);
   if (empty($base_uri)) $base_uri = sqm_baseuri();
   $loc = $base_uri . 'src/options.php?optpage=xx&optmode=submit&csp_change_success=no';
   if ($csp_was_already_encrypted_port == 443)
      $cancel_location = 'https://' . $HTTP_HOST . $loc;
   else if ($csp_was_already_encrypted_port)
      $cancel_location = 'https://' . $HTTP_HOST . ':' . $csp_was_already_encrypted_port . $loc;
   else if ($csp_non_standard_http_port)
      $cancel_location = 'http://' . $HTTP_HOST . ':' . $csp_non_standard_http_port . $loc;
   else
      $cancel_location = 'http://' . $HTTP_HOST . $loc;


   bindtextdomain('change_sqlpass', SM_PATH . 'locale');
   textdomain('change_sqlpass');


   echo '<table width="95%" align="center" cellpadding="2" cellspacing="2" border="0">'
      . '<tr><td bgcolor="';

   if (!$force)
      echo $color[0] . '">';
   else
      echo $color[2] . '">';


   if (!sqgetGlobalVar('SERVER_PORT', $SERVER_PORT, SQ_SERVER))
      $SERVER_PORT = 0;
   $is_https = ($csp_secure_port == $SERVER_PORT || (!$csp_secure_port && $SERVER_PORT == 443));


   echo '<center><b>';
   if ($is_https) 
      echo _("Change Password - Secure");
   else
      echo _("Change Password"); 
   echo '</b></center></td>';


   if (!empty($message_text))
      echo "<tr><td>\n$message_text</td></tr>\n";


?>


   <tr><td>


   <form method="post" action="">
   <table>
      <tr>
         <th align="right"><?php echo _("Old Password"); ?>:</th>
         <td><input type="password" name="cp_oldpass" value="" size="20"></td>
      </tr>
      <tr>
         <th align="right"><?php echo _("New Password"); ?>:</th>
         <td><input type="password" name="cp_newpass" value="" size="20"></td>
      </tr>
      <tr>
         <th align="right"><?php echo _("Verify New Password"); ?>:</th>
         <td><input type="password" name="cp_verify" value="" size="20"></td>
      </tr>
      <tr>
         <td align="right" colspan="2">
            <input type="hidden" name="csp_submit_change" value="1">
            <input type="submit" value="<?php echo _("Submit"); ?>">


<?php


   if (!$force)
      echo '<input type="button" value="' . _("Cancel") . '" onclick="document.location=\'' . $cancel_location . '\'" name="csp_submit_cancel">';


?>


         </td>
      </tr>
   </table>
   </td></tr>
   </tr></table>


<?php


   bindtextdomain('squirrelmail', SM_PATH . 'locale');
   textdomain('squirrelmail');


}



/**
  * Get salt used to encrypt password
  *
  * @return string The salt used to generate the 
  *                user's current/old password.
  *
  */
function get_password_salt()
{

   global $csp_salt_query, $csp_salt_static, $csp_debug;

   load_config('change_sqlpass', array('config.php'));

   if (!empty($csp_salt_static)) return $csp_salt_static;
   if (empty($csp_salt_query)) return '';


   // get database connection
   //
   $db = csp_get_database_connection();


   list($full_username, $user, $dom) = csp_parse_username();


   $sql = $csp_salt_query;
   $sql = str_replace(array('%1', '%2', '%3'), array($full_username, $user, $dom), $sql);
   $salt = $db->getAll($sql);


   // check for database errors
   //
   if (DB::isError($salt))
   {
      global $color;
      $msg = $salt->getMessage();
      $text = sprintf(_("DATABASE ERROR: could not lookup salt: %s"), ($csp_debug ? $sql . ' ---- ' . $msg : ''));
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
      plain_error_message($text, $color);
      exit;
   }


   // debug if needed
   //
   if ($csp_debug)
      echo sprintf(_("Password query result is: %s<br />Query was: %s<br /><br />"), $salt[0][0], $sql);


   // return salt
   //
   return $salt[0][0];

}



/**
  * Build encrypted password string
  *
  * @param string $password The password to encode
  *
  * @return string The given password in format ready for use in a
  *                database query.
  *
  */
function get_password_encrypt_string($password)
{

   global $password_encryption;

   load_config('change_sqlpass', array('config.php'));

   $salt = get_password_salt();

   switch (strtolower($password_encryption))
   {

      case strtolower(PASSWORD_ENCRYPTION_MYSQL_PASSWORD):
         return 'password("' . $password . '")';

      case strtolower(PASSWORD_ENCRYPTION_MYSQL_ENCRYPT):
         if (empty($salt))
            return 'encrypt("' . $password . '")';
         else
            return 'encrypt("' . $password . '", ' . $salt . ')';

      case strtolower(PASSWORD_ENCRYPTION_PHPCRYPT):
         if (empty($salt))
            return '"' . crypt($password) . '"';
         else
            return '"' . crypt($password, $salt) . '"';

      case strtolower(PASSWORD_ENCRYPTION_MD5CRYPT):
         return '"' . md5crypt($password, $salt) . '"';

      case strtolower(PASSWORD_ENCRYPTION_MD5):
         return '"' . md5($password) . '"';

      case strtolower(PASSWORD_ENCRYPTION_NONE):
      default:
         return '"' . $password . '"';

   }

}



/**
  * Validate password input
  *
  * @return array A list of messages describing problems
  *               found, or an empty array when everything
  *               validated.
  *
  */
function csp_validate_input()
{

   global $lookup_password_query, $csp_debug, $min_password_length, 
          $max_password_length, $include_digit_in_password,
          $include_uppercase_letter_in_password, $include_lowercase_letter_in_password,
          $include_nonalphanumeric_in_password;

   load_config('change_sqlpass', array('config.php'));


   $messages = array();

   sqgetGlobalVar('key', $key, SQ_COOKIE);
   sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

   $password = OneTimePadDecrypt($key, $onetimepad);

   sqgetGlobalVar('cp_oldpass', $cp_oldpass, SQ_POST);
   sqgetGlobalVar('cp_newpass', $cp_newpass, SQ_POST);
   sqgetGlobalVar('cp_verify', $cp_verify, SQ_POST);


   bindtextdomain('change_sqlpass', SM_PATH . 'locale');
   textdomain('change_sqlpass');


   if (empty($cp_oldpass))
      array_push($messages, _("You must type in your old password"));

   if (!empty($cp_oldpass) && $cp_oldpass != $password)
      array_push($messages, _("Your old password is not correct"));


   // extra password check just to be twice as safe
   //
   else if (!empty($cp_oldpass) && !empty($lookup_password_query))
   {
      
      // get database connection
      //
      $db = csp_get_database_connection();


      list($full_username, $user, $dom) = csp_parse_username();
      $encrypted_pwd = get_password_encrypt_string($db->escapeSimple($cp_oldpass));


      $sql = $lookup_password_query;
      $sql = str_replace(array('%1', '%2', '%3', '%4', '%5'), 
                         array($full_username, $user, $dom, $encrypted_pwd, $db->escapeSimple($cp_oldpass)),
                         $sql);
      $db_password = $db->getAll($sql);


      // check for database errors
      //
      if (DB::isError($db_password))
      {
         global $color;
         $msg = $db_password->getMessage();
         $text = sprintf(_("DATABASE ERROR: could not lookup old password: %s"), ($csp_debug ? $sql . ' ---- ' . $msg : ''));
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
         textdomain('squirrelmail');
         plain_error_message($text, $color);
         exit;
      }


      // debug if needed
      //
      if ($csp_debug)
         echo sprintf(_("Password query result is: %s<br />Query was: %s<br /><br />"), $db_password[0][0], $sql);


      // now check password
      //
      if (!$db_password[0][0])
         array_push($messages, _("Your old password does not match"));

   }


   if (empty($cp_newpass))
      array_push($messages, _("You must type in a new password"));

   // prevent SQL injection
   //
   if ($min_password_length && strlen($cp_newpass) < $min_password_length)
      array_push($messages, sprintf(_("Your new password is too short.  It must be at least %s characters long"), $min_password_length));

   if ($max_password_length && strlen($cp_newpass) > $max_password_length) 
      array_push($messages, sprintf(_("Your new password is too long.  It must be no more than %s characters long"), $max_password_length));

   if ($include_digit_in_password && !preg_match('/\d+/', $cp_newpass))
      array_push($messages, _("Please include at least one digit in your new password"));

   if ($include_uppercase_letter_in_password && !preg_match('/[A-Z]+/', $cp_newpass)) 
      array_push($messages, _("Please include at least one upper-case letter in your new password"));

   if ($include_lowercase_letter_in_password && !preg_match('/[a-z]+/', $cp_newpass))
      array_push($messages, _("Please include at least one lower-case letter in your new password"));

   if ($include_nonalphanumeric_in_password && !preg_match('/[^a-zA-Z0-9]+/', $cp_newpass))
      array_push($messages, _("Please include at least one non-alphanumeric character (such as @, - or _) in your new password"));

   if (empty($cp_verify))
      array_push($messages,
         _("You must also type in your new password in the verify box"));

   if (!empty($cp_newpass) && $cp_verify != $cp_newpass)
      array_push($messages, _("Your new password does not match the verify password"));

   if ($cp_oldpass == $cp_newpass)
      array_push($messages, _("Your new password must be different than your old password"));


   bindtextdomain('squirrelmail', SM_PATH . 'locale');
   textdomain('squirrelmail');


   return $messages;

}



/**
  * Change the password for current login user
  *
  * @param string $password The new password.
  *
  * @return boolean TRUE if the password was changed successfully
  *
  */
function change_password($password)
{

   global $password_update_queries, $csp_debug, $base_uri;

   load_config('change_sqlpass', array('config.php'));


   // get database connection
   //
   $db = csp_get_database_connection();


   list($full_username, $user, $dom) = csp_parse_username();
   $encrypted_pwd = get_password_encrypt_string($db->escapeSimple($password));


   // do all queries
   //
   foreach ($password_update_queries as $query)
   {

      $sql = $query;
      $sql = str_replace(array('%1', '%2', '%3', '%4', '%5'), 
                         array($full_username, $user, $dom, $encrypted_pwd, $db->escapeSimple($password)), 
                         $sql);


      // send query to database
      //
      $result = $db->query($sql);


      // check for database errors
      //
      if (DB::isError($result))
      {
         global $color;
         $msg = $result->getMessage();
         $text = sprintf(_("DATABASE ERROR: could not update password: %s"), ($csp_debug ? $sql . ' ---- ' . $msg : ''));
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
         textdomain('squirrelmail');
         plain_error_message($text, $color);
//         return FALSE;
         exit;
      }

   }


   // Write new cookies for the password
   // 
   if (empty($base_uri)) $base_uri = sqm_baseuri();
   $onetimepad = OneTimePadCreate(strlen($password));
   sqsession_register($onetimepad, 'onetimepad');
   $key = OneTimePadEncrypt($password, $onetimepad);
   sqsetcookie('key', $key, 0, $base_uri);


   return TRUE;

}



/**
  * Process password submission
  *
  * Retrieves password submission from POST
  * and redirects the page request as appropriate
  * if the change is successful, otherwise the
  * function will simply return normally.
  *
  * @return array An array of any error messages, if 
  *               input/validation problems were found
  *               (does not return if password was
  *               changed successfully)
  *
  */
function process_password_change_request()
{

   $messages = csp_validate_input();
   if (empty($messages))
   {

      sqgetGlobalVar('cp_newpass', $cp_newpass, SQ_POST);


      // if change is successful, redirect...
      //
      if (change_password($cp_newpass))
      {

         // if the session was previously HTTPS, make sure it stays as such
         //
         sqgetGlobalVar('csp_was_already_encrypted_port', $csp_was_already_encrypted_port, SQ_SESSION);
         sqgetGlobalVar('HTTP_HOST', $HTTP_HOST, SQ_SERVER);
         sqgetGlobalVar('csp_was_force_mode', $csp_was_force_mode, SQ_SESSION);
         global $base_uri;
         if (empty($base_uri)) $base_uri = sqm_baseuri();
         if (!empty($csp_was_force_mode))
            $loc = $base_uri . 'src/right_main.php';
         else
            $loc = $base_uri . 'src/options.php?optpage=xx&optmode=submit&csp_change_success=yes';
         if ($csp_was_already_encrypted_port == 443)
            $redirect_location = 'https://' . $HTTP_HOST . $loc;
         else if ($csp_was_already_encrypted_port)
            $redirect_location = 'https://' . $HTTP_HOST . ':' . $csp_was_already_encrypted_port . $loc;
         else if ($csp_non_standard_http_port)
            $redirect_location = 'http://' . $HTTP_HOST . ':' . $csp_non_standard_http_port . $loc;
         else
            $redirect_location = 'http://' . $HTTP_HOST . $loc;


         sqsession_unregister('csp_was_force_mode');
         session_write_close();
         header('Location: ' . $redirect_location);
         echo "\n\n";
         exit;

      }

   }

   return $messages;

}



?>
