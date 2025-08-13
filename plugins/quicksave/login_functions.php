<?php


/**
  * SquirrelMail Quick Save Plugin
  * Copyright (c) 2001-2002 Ray Black <allah@accessnode.net>
  * Copyright (c) 2003-2010 Paul Lesniewski <paul@squirrelmail.org>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage quicksave
  *
  */



/**
  * Determine whether or not to offer message 
  * recovery upon login
  *
  */
function quicksave_check_recovery_upon_login_do($args)
{

   global $javascript_on;
   if (!$javascript_on) return FALSE;

   if (!sqgetGlobalVar('just_logged_in', $just_logged_in, SQ_SESSION)
    || empty($just_logged_in))
      return FALSE;


   global $show_message_recovery_alert_in_motd, $motd,
          $show_message_body_on_recover_motd_notice,
          $default_cookie_encryption, $user_can_override_encryption,
          $username, $data_dir, $show_message_details_in_motd;

   include_once(SM_PATH . 'plugins/quicksave/common_functions.php');
   quicksave_init();


   // don't bother if not enabled
   //
   if (!$show_message_recovery_alert_in_motd)
      return FALSE;


   if (!cookie_pull('is_active', FALSE))
      return FALSE;


   $encryption = $default_cookie_encryption;
   if ($user_can_override_encryption)
      $encryption = getPref($data_dir, $username, 'quicksave_encryption', $encryption);

   // can't show message details in MOTD until we fix the
   // PHP transcription of the decryption function for the
   // medium level
   //
   if ($encryption == 'medium')
      $show_message_details_in_motd = FALSE;


   // get all compose fields from cookie
   //
   $send_to_contents = trim(cookie_pull('send_to', TRUE));
   $send_to_cc_contents = trim(cookie_pull('send_cc', TRUE));
   $send_to_bcc_contents = trim(cookie_pull('send_bcc', TRUE));
   $subject_contents = trim(cookie_pull('subject', TRUE));
   $body_contents = trim(cookie_pull('body', TRUE));


   // only offer to restore if there was any data there
   //
   if (empty($send_to_contents) && empty($send_to_cc_contents)
    && empty($send_to_bcc_contents) && empty($subject_contents)
    && empty($body_contents))
      return FALSE;


   // format strings to be shown in alert
//TODO: length limitations below are from JavaScript; probably need to fine-tune them for fitting in the MOTD area (longer)
   //
   sq_change_text_domain('quicksave');
   if (strlen($send_to_contents) > 40)
      $send_to_contents = substr($send_to_contents, 0, 35) . '...';
   else if (empty($send_to_contents))
      $send_to_contents = _("<none>");
   if (strlen($subject_contents) > 50)
      $subject_contents = substr($subject_contents, 0, 45) . '...';
   else if (empty($subject_contents))
      $subject_contents = _("<none>");
   if ($show_message_body_on_recover_motd_notice)
   {
      if (strlen($body_contents) > 80)
         $body_contents = substr($body_contents, 0, 75) . '...';
   }
   else $body_contents = '';


   if (check_sm_version(1, 5, 2))
   {
      global $oTemplate;
      $oTemplate->assign('show_message_details_in_motd', $show_message_details_in_motd);
      $oTemplate->assign('motd_pad', (strlen($motd) > 0));
      $oTemplate->assign('send_to_contents', $send_to_contents);
      $oTemplate->assign('send_to_cc_contents', $send_to_cc_contents);
      $oTemplate->assign('send_to_bcc_contents', $send_to_bcc_contents);
      $oTemplate->assign('subject_contents', $subject_contents);
      $oTemplate->assign('body_contents', $body_contents);
      $oTemplate->assign('compose_uri', makeComposeLink('src/compose.php?qs_recover=1',
                                                        _("Click here to resume it.")),
                         FALSE);
      $output = $oTemplate->fetch('plugins/quicksave/motd_recover_alert.tpl');
      sq_change_text_domain('squirrelmail');
      $motd .= ' '; // trick so other plugins know MOTD has been added to
      return array('motd_inside' => $output);
   }
   else
   {
      if ($show_message_details_in_motd)
         $motd .= (empty($motd) ? '' : '<br /><br />')
               . _("NOTE: The following email was interrupted and was never sent:")
               . '<br />&nbsp;&nbsp;'
               . _("To:") . '&nbsp;'
               . $send_to_contents
               . '<br />&nbsp;&nbsp;'
               . _("Subject:") . '&nbsp;'
               . $subject_contents
               . (!empty($body_contents) ? '<br />' : '')
               . $body_contents
               . '<br />'
               . makeComposeLink('src/compose.php?qs_recover=1',
                                 _("Click here to resume it."));
      else
         $motd .= _("NOTE: You have an unsent message that was interrupted.")
               . '<br />'
               . makeComposeLink('src/compose.php?qs_recover=1',
                                 _("Click here to resume it."));
   }

   sq_change_text_domain('squirrelmail');

}



/**
  * Try to grab stored quicksave cookie
  *
  * @param string  $name    The name of the cookie to pull
  * @param boolean $decrypt Whether or not to decrypt the 
  *                         retrieved value before returning it
  *
  * @return string The value found for the requested
  *                cookie, or an empty string if not found
  *
  */
function cookie_pull($name, $decrypt)
{

   global $username, $data_dir, $default_cookie_encryption, 
          $user_can_override_encryption, $useMultipleCookies;

   include_once(SM_PATH . 'plugins/quicksave/common_functions.php');
   quicksave_init();

   $encryption = $default_cookie_encryption;
   if ($user_can_override_encryption)
      $encryption = getPref($data_dir, $username, 'quicksave_encryption', $encryption);


   $cookie_value = '';
   $qs_username = preg_replace('/[\.@_]/', '', $username);


   // get cookie first
   //
   // TODO: urlencode() may not produce same result as the
   // JavaScript escape() function; may produce anomalies...?
   //
   sqGetGlobalVar(urlencode('QS' . $qs_username) . $name, $cookie_value, SQ_COOKIE);
   $cookie_value = urldecode($cookie_value);


   // if using multiple cookie storage, try to piece together
   // more than one cookie
   //
   if ($useMultipleCookies)
   {

      if (empty($cookie_value))
      {

         $cookieCount = 0;
         $cookie_value = '';

         while (sqGetGlobalVar(urlencode('QS' . $qs_username) . $name . (++$cookieCount), 
                               $cookie_crumb, SQ_COOKIE))
         {
            $cookie_value .= urldecode($cookie_crumb);
         }

      }

   }


   // if no decryption is needed, return immediately
   //
   if (!$decrypt || $encryption == 'none') return $cookie_value;


   // decrypt
   //
   switch ($encryption)
   {
      case 'low'      : return decrypt_low($cookie_value);
      case 'medium'   : return decrypt_medium($cookie_value);
      case 'moderate' : return decrypt_moderate($cookie_value);
      default         : die('QuickSave encryption level not understood');
   }

}



/**
  * Decrypt low
  *
  * This function is a PHP-transcribed version of the
  * basic/simple ascii encryption JavaScript function 
  * taken and slightly modified from javascript.com:
  * http://javascript.internet.com/passwords/character-encoder.html
  * Original:  Mike McGrath (mike_mcgrath@lineone.net)
  * Web Site:  http://website.lineone.net/~mike_mcgrath/
  *
  * @param string $value The value to decrypt
  *
  * @return The decrypted value
  *
  */
function decrypt_low($value)
{

   if (empty($value)) return '';

   $decrypted_string = '';

   for ($i = 0; $i < strlen($value); $i += 2) 
   {
      $num_in = substr($value, $i, 2) + 23;
// TODO: urldecode() may not produce same result as 
// the JavaScript unescape() function; may produce 
// anomalies...?
      $num_in = urldecode('%' . base_convert($num_in, 10, 16));
      $decrypted_string .= $num_in;
   }

   return urldecode($decrypted_string);

}



/**
  * Decrypt medium
  *
  * This function is a PHP-transcribed version of the
  * basic/simple ascii encryption JavaScript function 
  * taken and slightly modified from javascript.com:
  * http://javascript.internet.com/passwords/ascii-encryption.html
  * Original:  David Salsinha (david.salsinha@popsi.pt)
  *
  * @param string $value The value to decrypt
  *
  * @return The decrypted value
  *
  */
function decrypt_medium($value)
{
//TODO: currently very broken

   if (empty($value)) return '';

// TODO: urldecode() may not produce same result as 
// the JavaScript unescape() function; may produce 
// anomalies...?
// In fact, the problem seems rather to be that PHP is
// not seeing the same characters in the string that
// it grabs from the cookie.  JavaScript uses UTF-16 
// and PHP UTF-8, and that might be part of the issue; 
// in any case, PHP seems to decode the cookie very 
// differently than JavaScript, and unless that can be 
// fixed, it doesn't look like there's much use in trying
// to make up the difference in this function...
// below was much mucking about, trying to avoid using
// mbstring, etc., but I don't know what I'm doing
   $value = urldecode($value);

   $decrypted_string = '';
   $temp = array();
   $temp2 = array();
   $length = strlen($value);

   for ($i = 0; $i < $length; $i++) 
   {
      $temp[$i] = ord($value{$i});
      $temp2[$i] = isset($value{($i + 1)}) ? ord($value{($i + 1)}) : 0;
      //temp[$i] = uniord($value{$i});
      //temp2[$i] = isset($value{($i + 1)}) ? uniord($value{($i + 1)}) : 0;
   }

   for ($i = 0; $i < $length; $i = $i + 2) 
   {
      $decrypted_string .= chr($temp[$i] - $temp2[$i]);
      //decrypted_string .= unichr($temp[$i] - $temp2[$i]);
   }

   return urldecode($decrypted_string);

}



/**
  * Unicode-capable chr()
  *
  * Swiped from user comments at:
  * http://php.net/manual/function.chr.php
  *
  */
function unichr($code)
{
// TODO: not tested
   if ($code < 128) {
      $utf = chr($code);
   } else if ($code < 2048) {
      $utf = chr(192 + (($code - ($code % 64)) / 64));
      $utf .= chr(128 + ($code % 64));
   } else {
      $utf = chr(224 + (($code - ($code % 4096)) / 4096));
      $utf .= chr(128 + ((($code % 4096) - ($code % 64)) / 64));
      $utf .= chr(128 + ($code % 64));
   }
   return $utf;

// TODO: not tested - alternative method
// is this a better way? it only works for PHP 4.3.0+
   return html_entity_decode('&#' . $code . ';', ENT_NOQUOTES, 'UTF-8');
}



/**
  * Unicode-capable ord()
  *
  * Swiped from user comments at:
  * http://php.net/manual/function.ord.php
  *
  * @Algorithm: http://www1.tip.nl/~t876506/utf8tbl.html
  * @Logic: UTF-8 to Unicode conversion
  * 
  */
function uniord($char)
{
   $ud = 0;

   if (ord($char{0})>=0 && ord($char{0})<=127)
      $ud = ord($char{0});
   if (ord($char{0})>=192 && ord($char{0})<=223)
      $ud = (ord($char{0})-192)*64 + (ord($char{1})-128);
   if (ord($char{0})>=224 && ord($char{0})<=239)
      $ud = (ord($char{0})-224)*4096 + (ord($char{1})-128)*64 + (ord($char{2})-128);
   if (ord($char{0})>=240 && ord($char{0})<=247)
      $ud = (ord($char{0})-240)*262144 + (ord($char{1})-128)*4096 + (ord($char{2})-128)*64 + (ord($char{3})-128);
   if (ord($char{0})>=248 && ord($char{0})<=251)
      $ud = (ord($char{0})-248)*16777216 + (ord($char{1})-128)*262144 + (ord($char{2})-128)*4096 + (ord($char{3})-128)*64 + (ord($char{4})-128);
   if (ord($char{0})>=252 && ord($char{0})<=253)
      $ud = (ord($char{0})-252)*1073741824 + (ord($char{1})-128)*16777216 + (ord($char{2})-128)*262144 + (ord($char{3})-128)*4096 + (ord($char{4})-128)*64 + (ord($char{5})-128);
   if (ord($char{0})>=254 && ord($char{0})<=255) //error
      $ud = false;

   return $ud;

// TODO: not tested - alternative method
// needs PHP 4.1.0
// no, this would need a full translation table to work...
   $ret = htmlentities($char, ENT_NOQUOTES, 'UTF-8');
   $ret = convert_html_entities_to_unicode_entities($ret);
   return trim($ret, '&#;');

}
function convert_html_entities_to_unicode_entities($string, $quote_style=ENT_COMPAT)
{
   $htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
   $entitiesDecoded = array_keys   (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
   $num = count ($entitiesDecoded);
   for ($u = 0; $u < $num; $u++) {
      $utf8Entities[$u] = '&#'.ord($entitiesDecoded[$u]).';';
   }
   return str_replace ($htmlEntities, $utf8Entities, $string);

// TODO: not tested - alternative method
   $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);

   foreach ($trans as $key => $value)
      $trans[$key] = '&#' . ord($key) . ';';

   return strtr($string, $trans);
}



/**
  * Decrypt moderate
  *
  * This function is a PHP-transcribed version of the
  * XOR encryption JavaScript function taken from 
  * javascript.com:
  * http://javascript.internet.com/passwords/xor-encryption4.html
  * Copyright 2001 by Terry Yuen.
  * Email: kaiser40@yahoo.com
  * Last update: July 15, 2001
  *
  * @param string $value The value to decrypt
  *
  * @return The decrypted value
  *
  */
function decrypt_moderate($value)
{

   if (empty($value)) return '';

   global $username;

   $pwd = $username;

   if (strlen($value) < 5)
   {
      die(_("QuickSave Error - A salt value could not be extracted from the encrypted message\\nbecause its length is too short.  The message cannot be decrypted.\\nPlease contact your system administrator."));
   }
   if (empty($pwd))
   {
      die(_("QuickSave Error - No decryption password given.  Please contact your system administrator."));
   }


   $prand = '';
   for ($i = 0; $i < strlen($pwd); $i++)
   {
      $prand .= ord($pwd{$i});
//TODO: not sure if we will lose (UTF) encoding here...
//      was: prand += pwd.charCodeAt(i).toString();
//      $prand .= uniord($pwd{$i});
   }

   $divisor = 5;

   while ($divisor > 1)
   {
      $mult = '';
      $sPos = floor(strlen($prand) / $divisor);
      $found_non_zero = FALSE;
      for ($i = 1; $i <= $divisor; $i++)
      {
         $char = $prand{($sPos > 0 ? $sPos * $i - 1 : 0)};
         if ($char != '0' && $char != '')
         {
            $found_non_zero = TRUE;
         }
         $mult .= $char;
      }

      if ($found_non_zero) break;

      $divisor--;
   }

   $incr = round(strlen($pwd) / 2);
   $modu = pow(2, 31) - 1;
   $salt = base_convert(substr($value, strlen($value) - 8), 16, 10);
   $value = substr($value, 0, strlen($value) - 8);
   $prand .= $salt;

   // I think the original JavaScript is borked; the first time it
   // does this math, it correctly takes the full numbers and adds
   // them, but after that, it truncates the numbers after either
   // a decimal point or the scientific notator "e"; so this (and
   // the while loop below that uses intval()) is a hack to match
   // that behavior (well, there is one more hitch, which is that
   // PHP's precision doesn't quite match JavaScript's, so we try
   // to use gmp_add() to rectify that, but if not available (ala
   // PHP 4 on Windows), then we use an even more horrible hack,
   // where we just make sure the number is big enough and know that
   // the addition won't usually change the result, which will be
   // the rounded value of digits 10 thru 25)
   //
   // 50 is somewhat arbitrary
   //
   if (strlen($prand) > 10)
   {
      if (function_exists('gmp_add'))
         $prand = gmp_strval(gmp_add(substr($prand, 0, 10), substr($prand, 10)));
      else if (strlen($prand) > 50)
         $prand = substr($prand, 10, 17);

      // manual rounding.  yuck
      //
      if (isset($prand{16}))
         $prand = $prand{0} . '.' . substr($prand, 1, 14)
                . ($prand{16} > 4 ? $prand{15} + 1 : $prand{15});
   }

   while (strlen($prand) > 10)
   {
      $prand = intval(substr($prand, 0, 10)) + intval(substr($prand, 10));
   }

   // not enough precision in modulus operation, so we do it manually:
   //$prand = ($mult * $prand + $incr) % $modu;
   $div = (int)((($mult * $prand) + $incr) / $modu);
   $prand = (($mult * $prand) + $incr) - ($div * $modu);

   $enc_chr = '';
   $enc_str = '';
   for ($i = 0; $i < strlen($value); $i += 2)
   {
      $enc_chr = base_convert(substr($value, $i, 2), 16, 10)
               ^ floor(($prand / $modu) * 255);

      $enc_str .= chr($enc_chr);
//TODO: not sure if we will lose (UTF) encoding here...
//      was: enc_str += String.fromCharCode(enc_chr);
//      $enc_str .= unichr($enc_chr);

      // not enough precision in modulus operation, so we do it manually:
      //$prand = ($mult * $prand + $incr) % $modu;
      $div = (int)((($mult * $prand) + $incr) / $modu);
      $prand = (($mult * $prand) + $incr) - ($div * $modu);
   }

   return urldecode($enc_str);

}



