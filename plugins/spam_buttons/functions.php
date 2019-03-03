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
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function spam_buttons_init()
{

   return load_config('spam_buttons',
                      array('config.php', '../../config/config_spam_buttons.php'),
                      TRUE);

}



/**
  * Retrieve All Message Headers
  *
  * When called more than once in the same page request against the same
  * message, a cached set of headers is returned for efficiency.
  *
  * @param int    $passed_id     UID of the desired message
  * @param string $passed_ent_id Entity ID of the desired message attachment
  *
  * @return array An array of all message headers
  *
  */
function sb_get_message_headers($passed_id, $passed_ent_id)
{

   static $parsed_headers = array();

   $index = $passed_id . '.' . $passed_ent_id;

   if (isset($parsed_headers[$index]))
      return $parsed_headers[$index];

   global $imapConnection, $username, $key, $uid_support,
          $mbx_response, $imapServerAddress, $imapPort, $mailbox; 

   if (check_sm_version(1, 5, 2)) { $key = FALSE; $uid_support = TRUE; }

   if (!is_resource($imapConnection))
      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   if (empty($mbx_response))
      $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);

   if (empty($passed_ent_id))
      $headers = sqimap_run_command($imapConnection, "FETCH $passed_id BODY.PEEK[HEADER]", true, $response, $message, $uid_support);
   else
      $headers = sqimap_run_command($imapConnection, "FETCH $passed_id BODY.PEEK[$passed_ent_id.HEADER]", true, $response, $message, $uid_support);


   $parsed_headers[$index] = sb_parse_headers($headers);
   return $parsed_headers[$index];

}



/**
  * Parse raw headers retrieved from IMAP server into internal array format
  *
  * @param string $headers The raw headers straight from an IMAP FETCH query
  *
  * @return array An array of the given message headers
  *
  */
function sb_parse_headers($headers)
{

   $parsed_headers = array();

   // based on src/view_header.php...
   //
   $first = $second = array();
   $cnum = 0;

   for ($i=1; $i < count($headers); $i++) 
   {
      $line = $headers[$i];
      switch (true) 
      {
         case (eregi("^&gt;", $line)):
            $second[$i] = $line;
            $first[$i] = '&nbsp;';
            $cnum++;
            break;
         case (eregi("^[ |\t]", $line)):
            $second[$i] = $line;
            $first[$i] = '';
            break;
         case (eregi("^([^:]+):(.+)", $line, $regs)):
            $first[$i] = $regs[1] . ':';
            $second[$i] = $regs[2];
            $cnum++;
            break;
         default:
            $second[$i] = trim($line);
            $first[$i] = '';
            break;
      }
   }

   for ($i=0; $i < count($second); $i = $j) 
   {
      $f = (isset($first[$i]) ? $first[$i] : '');
      $s = (isset($second[$i]) ? $second[$i] : '');
      $j = $i + 1;
      while (($first[$j] == '') && ($j < count($first))) 
      {
         $s .= ' ' . $second[$j];
         $j++;
      }
      if ($f) 
         $parsed_headers[] = array($f,$s);
   }

   return $parsed_headers;

}



/**
  * Retrieve A Single Message Header
  *
  * When called more than once in the same page request against the same
  * message, a cached header value is returned for efficiency.
  *
  * @param int    $passed_id     UID of the desired message
  * @param string $passed_ent_id Entity ID of the desired message attachment
  * @param string $header_name   The name of the desired header
  *
  * @return array An array containing the header name and its value,
  *               or an empty array if the header was not found
  *
  */
function sb_get_message_header($passed_id, $passed_ent_id, $header_name)
{

   static $parsed_header = array();

   $index = $passed_id . '.' . $passed_ent_id . '.' . $header_name;

   if (isset($parsed_header[$index]))
      return $parsed_header[$index];

   global $imapConnection, $username, $key, $uid_support,
          $mbx_response, $imapServerAddress, $imapPort, $mailbox;

   if (check_sm_version(1, 5, 2)) { $key = FALSE; $uid_support = TRUE; }

   if (!is_resource($imapConnection))
      $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   if (empty($mbx_response))
      $mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);

   if (empty($passed_ent_id))
      $header = sqimap_run_command($imapConnection, "FETCH $passed_id BODY.PEEK[HEADER.FIELDS ($header_name)]", true, $response, $message, $uid_support);
   else
      $header = sqimap_run_command($imapConnection, "FETCH $passed_id BODY.PEEK[$passed_ent_id.HEADER.FIELDS ($header_name)]", true, $response, $message, $uid_support);


   $parsed_header[$index] = sb_parse_headers($header);
   if (!empty($parsed_header[$index][0]))
      $parsed_header[$index] = $parsed_header[$index][0];
   else
      $parsed_header[$index] = array();
   return $parsed_header[$index];

}



