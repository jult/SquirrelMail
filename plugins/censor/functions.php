<?php

/**
  * SquirrelMail Censor Plugin
  * Copyright (c) 2004-2007 Paul Lesniewski <paul@squirrelmail.org>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage censor
  *
  */



/**
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function censor_init()
{

   if (!@include_once (SM_PATH . 'plugins/censor/config.php'))
      if (!@include_once (SM_PATH . 'plugins/censor/config.sample.php'))
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
function censor_configtest_do()
{

   // make sure base config is available
   //
   if (!censor_init())
   {
      do_err('Censor plugin is missing its main configuration file', FALSE);
      return TRUE;
   }


   // make sure backend is supported
   //
   global $censor_backend;
   $function_name = 'load_word_lists_' . $censor_backend;
   if (!function_exists($function_name))
   {
      do_err('Censor plugin is misconfigured - unknown backend "' . $censor_backend . '"', FALSE);
      return TRUE;
   }


   // check that we have at least one word list
   //
   $word_lists = load_word_lists();
   if (empty($word_lists[0]) && empty($word_lists[1]))
   {
      do_err('Censor plugin is missing its word lists', FALSE);
      return TRUE;
   }


   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically, but also don't bother if
   // 1.4.10+
   //
   if (!check_sm_version(1, 4, 10) 
    || (check_sm_version(1, 5, 0) && !check_sm_version(1, 5, 2)))
   {

      // try to find Compatibility, and then that it is v2.0.7+
      //
      if (function_exists('check_plugin_version')
       && check_plugin_version('compatibility', 2, 0, 7, TRUE))
         return FALSE;


      // something went wrong
      //
      do_err('Censor plugin requires the Compatibility plugin version 2.0.7+', FALSE);
      return TRUE;

   }

   return FALSE;

}



/**
  * Displays any errors that blocked email from
  * sending back on top of compose screen
  *
  */
function censor_error_on_compose_screen_do()
{

   global $PHP_SELF, $use_signature;


   // only want to do this on the compose page
   //
   if (stristr($PHP_SELF, '/src/compose.php'))
   {

      global $censored, $color;
      if (sqgetGlobalVar('censored', $censored, SQ_GET) && $censored)
      {

         // don't need to add signature again
         //
         $use_signature = FALSE;


         // TODO: not sure if this will work with all
         // PHP versions, especially 4.3.x....
         //
         // Need to avoid endless loops (plain_error_message()
         // ends up calling to displayPageHeader(), which is
         // where the current hook call is located, so this
         // code gets called recursively if we don't stop it
         // here...
         //
         global $countMe;
         if (!$countMe)
         {
            $countMe = 1;
            sq_change_text_domain('censor');

            $msg = _("Your message contains some inappropriate words, which have been removed.  Click send again to send your message after reviewing it.");
            if (check_sm_version(1, 5, 2))
            {
               global $censor_output, $squirrelmail_plugin_hooks;
               $censor_output = error_box($msg, NULL, TRUE);
               $squirrelmail_plugin_hooks['compose_header_bottom']['censor'] = 'censor_error_output';
               $squirrelmail_plugin_hooks['page_header_bottom']['censor'] = 'censor_error_output';
            }
            else
               plain_error_message($msg, $color);

            sq_change_text_domain('squirrelmail');
            return;
         }

      }

   }

}



/**
  * Show output below SM page header, for displaying errors in 1.5.2+
  *
  * Output should already be constructed (by using templates), sanitized
  * and ready to go, stored in global $censor_output variable.
  *
  */
function censor_error_output()
{
   global $censor_output;
   echo $censor_output;
}



/**
  * Checks outgoing mail content for banned words.
  *
  */
function censor_compose_form_do(&$args)
{

   global $censor_replacement, $censor_match_profanitiy_length,
          $censor_advanced, $censor_space_is_punctuation, 
          $censor_all_harsh, $censor_stop_outgoing_mail;


   // load config
   //
   censor_init();


   // grab message subject and body
   //
   if (check_sm_version(1, 5, 2))
      $message = &$args;
   else
      $message = &$args[1];

   $subject = $message->rfc822_header->subject;
   $body = $message->body_part;


   // load up word lists
   //
   $word_lists = load_word_lists();


   // now is where the fascist steps in
   //
   $censored_subject = strip_profanities($word_lists[0], 
                                         $word_lists[1],
                                         $subject, 
                                         $censor_replacement,
                                         $censor_match_profanitiy_length,
                                         $censor_advanced,
                                         $censor_space_is_punctuation,
                                         $censor_all_harsh);
   $censored_body = strip_profanities($word_lists[0], 
                                      $word_lists[1],
                                      $body, 
                                      $censor_replacement,
                                      $censor_match_profanitiy_length,
                                      $censor_advanced,
                                      $censor_space_is_punctuation,
                                      $censor_all_harsh);


   // note if changes were made
   //
   $changed_subject = ($subject != $censored_subject);
   $changed_body = ($body != $censored_body);


   // stop outgoing message if needed
   //
   if ($censor_stop_outgoing_mail
    && ($subject != $censored_subject
     || $body != $censored_body))
   {
      global $color, $send_to, $send_to_cc, $send_to_bcc, $subject, $body,
             $variable_sent_folder, $PHP_SELF;
      sqgetGlobalVar('variable_sent_folder', $variable_sent_folder);
      $subject = $censored_subject;
      $body = $censored_body;

      if (strpos($PHP_SELF, '?') === FALSE)
         $sep = '?';
      else
         $sep = '&';

      header('Location: ' . $PHP_SELF . $sep . 'send_to=' . urlencode($send_to)
         . '&send_to_cc=' . urlencode($send_to_cc) . '&send_to_bcc='
         . urlencode($send_to_bcc) . '&subject=' . urlencode($subject)
         . '&censored=1&body=' . urlencode($body)
         . censor_buildQueryString('mailprio') 
         . censor_buildQueryString('delete_draft')
         . censor_buildQueryString('identity')
         // doesn't pull default so just forget it! . censor_buildQueryString('variable_sent_folder')
         . censor_buildQueryString('session')
         // makes query string too big . censor_buildQueryString('restoremessages')
         . censor_buildQueryString('request_mdn') 
         . censor_buildQueryString('request_dr')
         . censor_buildQueryString('passed_id') 
         . censor_buildQueryString('custom_from')
         . censor_buildQueryString('mailbox') 
         . censor_buildQueryString('passed_ent_id')
             // SM 1.2.x : reply_id and forward_id
         . censor_buildQueryString('startMessage') 
         . censor_buildQueryString('reply_id')
         . censor_buildQueryString('forward_id'));

      exit;
   }


   // otherwise, just return modified message
   //
   $message->rfc822_header->subject = $censored_subject;
   $message->body_part = $censored_body;
   return $message;

}



/**
  * Load word lists
  *
  * Word lists are loaded into two categories: "tame"
  * and "harsh".  The word lists are loaded from 
  * the source indicated in the plugin configuration.
  * One or the other can be empty (not defined), but 
  * not both should not.
  *
  * @return array An array containing two sub-arrays,
  *               one for each the "tame" profanities,
  *               and the "harsh" profanities word 
  *               lists (in that order).
  *
  */
function load_word_lists()
{

   global $censor_backend;

   censor_init();

   $function_name = 'load_word_lists_' . $censor_backend;

   if (function_exists($function_name))
      return $function_name();

   else
      die('Censor plugin backend "' . $censor_backend . '" unknown');

}



/**
  * Load word lists from file
  *
  * Word lists are loaded into two categories: "tame"
  * and "harsh".  The word lists are loaded from 
  * the files specified in the plugin configuration
  * for the "file" backend.  One or the other can
  * be empty (not defined), but not both should not.
  *
  * @return array An array containing two sub-arrays,
  *               one for each the "tame" profanities,
  *               and the "harsh" profanities word 
  *               lists (in that order).
  *
  */
function load_word_lists_file()
{

   global $tame_profanities_file, $harsh_profanities_file;

   censor_init();

   static $tame_profanities;
   static $harsh_profanities;

   if (empty($tame_profanities))
   {
      if (!empty($tame_profanities_file) 
       && is_readable($tame_profanities_file)) 
         $tame_profanities = file($tame_profanities_file);
      else
         $tame_profanities = array();
   }

   if (empty($harsh_profanities))
   {
      if (!empty($harsh_profanities_file) 
       && is_readable($harsh_profanities_file)) 
         $harsh_profanities = file($harsh_profanities_file);
      else
         $harsh_profanities = array();
   }

   return array($tame_profanities, $harsh_profanities);

}



/**
  * Strip (Profane) Language From String
  *
  * Copyright (c) 2006 Paul Lesniewski <paul@squirrelmail.org>
  * Licensed under the GNU GPL. For full terms, see:
  * http://http://www.gnu.org/copyleft/gpl.html
  *
  * Does a best-effort attempt at removing "profane" words
  * from the given string.  Application of this function
  * need not be limited to "profane" word lists, as the
  * words are loaded externally and can be of your choosing.
  *
  * Note that it may also be possible to use soundex(),
  * metaphone(), levenshtein(), and/or similar_text() to 
  * accomplish even more fuzzy filtering, but these are 
  * not implemented here since we typically do not want
  * to get *that* fuzzy with user input.  Perhaps some day
  * those may be added as configurable options.
  *
  * Basic functionality herein is as follows:
  *
  * A word list with what is considered to be less 
  * offensive words ($tame_profanities) is first
  * used to scan the target string, each as a stand-alone 
  * word - when embedded in other words, these are igored.  
  * If $all_harsh is turned on, then these words too are 
  * treated as "harsh" words per the following:
  *
  * A word list with what is considered to be more
  * offensive words ($harsh_profanities) is then 
  * used to scan the target string, each as both a 
  * stand-alone word AND as embedded in other words.
  *
  * If $advanced is turned on, each word will also be 
  * checked with possible punctuation embedded in it,
  * so that something like "c-r-a-p" or "c_r-ap" will 
  * be caught and censored.  If $space_is_punctuation
  * is turned on, "c r a p" would also be caught.
  *
  * Replacements can be done so that a fixed string 
  * is inserted in place of each profanity, or such
  * that the replacement string is inserted once for
  * each letter in the profanity.  This behavior is 
  * determined by the $match_profanity_length 
  * parameter.
  *
  * If you provide a replacement string that might
  * be included in one of the word lists, then this
  * will possibly blow up.  There is a failsafe 
  * check that will skip any such word from the word 
  * lists.  You can comment that code out if you like
  * to live danerously or you are confident in your
  * ability to choose good replacement strings.
  *
  * Note that the words list files used herein should have 
  * one "profane" word per line.  All tests are done
  * case insensitively.
  *
  * @param array   $tame_profanities       A word list containing
  *                                        "tame" profanities.
  * @param array   $harsh_profanities      A word list containing
  *                                        "harsh" profanities.
  * @param string  $the_string             The string to be 
  *                                        censored.
  * @param string  $replacement            The string that 
  *                                        will be used to 
  *                                        replace any profanity
  *                                        (OPTIONAL; default
  *                                        is the string "**").
  * @param boolean $match_profanity_length When turned on, the
  *                                        replacement string
  *                                        will be duplicated
  *                                        for each letter in
  *                                        the profanity, 
  *                                        otherwise, $replacement
  *                                        will be inserted just 
  *                                        once per profanity
  *                                        (OPTIONAL; default is
  *                                        to only use a static
  *                                        replacement string).
  * @param boolean $advanced               Whether or not a more 
  *                                        exhaustive method should 
  *                                        be used (OPTIONAL;
  *                                        default is to just  
  *                                        do a simple and fast
  *                                        filtering).
  * @param boolean $space_is_punctuation   Whether or not spaces
  *                                        should be used as part
  *                                        of the punctuation used
  *                                        in the advanced 
  *                                        filtering (OPTIONAL;
  *                                        default is to use spaces
  *                                        as part of punctuation).
  * @param boolean $all_harsh              Whether or not all 
  *                                        words should be
  *                                        filtered out even
  *                                        when embedded in 
  *                                        other words, including 
  *                                        "tame" profanities
  *                                        (OPTIONAL; default 
  *                                        is not to filter
  *                                        "tame" profanities 
  *                                        embedded in other words).
  *
  * @return string The censored string.
  *
  */
function strip_profanities($tame_profanities, $harsh_profanities,
                           $the_string, $replacement='**', 
                           $match_profanity_length=FALSE,
                           $advanced=FALSE, $space_is_punctuation=TRUE,
                           $all_harsh=FALSE)
{

   if (!is_array($tame_profanities) || !is_array($harsh_profanities))
      die('strip_profanities(): $tame_profanities or $harsh_profanities is not an array');
 
   //$punctuation = '-!@#$%^&*()|\\;:\'"\/.,<>+=_[\]{}`~?';
   //if ($space_is_punctuation)
   //   $punctuation .= ' ';

   $punctuation = '^a-z0-9';
   if (!$space_is_punctuation)
      $punctuation .= ' ';

   if ($all_harsh)
   {
      $harsh_profanities = array_merge($harsh_profanities, $tame_profanities);
      $tame_profanities = array();
   }


   // look for one profanity at a time - tame first
   //
   foreach ($tame_profanities as $profanity)
   {

      $profanity = trim($profanity);


      // for advanced filtering, insert possible punctuation
      // between each character in the target word
      //
      if ($advanced)
      {
         $pattern = '';
         for ($i = 0; $i < strlen($profanity); $i++)
            $pattern .= $profanity{$i}
                     . ($i < strlen($profanity) - 1 ? '[' . $punctuation . ']{0,}' : '');
      }
      else
         $pattern = $profanity;


      // is this profanity going to catch the replacement?
      // skip it if so
      //
      if (preg_match('/\b' . $pattern . '\b/i', $replacement))
         continue;


      // now just do the replacement
      //
      if ($match_profanity_length)
         $the_string = preg_replace('/\b' . $pattern . '\b/ie', 
                                    // I swear this should be \\1 and not \\0,
                                    // the PHP manual supports that "feeling",
                                    // but my development environment begs
                                    // to differ... ymmv
                                    'preg_replace("/./", $replacement, "\\0")', 
                                    $the_string);
      else
         $the_string = preg_replace('/\b' . $pattern . '\b/i', 
                                    $replacement, 
                                    $the_string);

   }


   // now "harsh" ones
   //
   // this code can/should probably be refactored, since
   // the only difference from the above is the lack of
   // the word boundary assertions ("\b") on both sides
   // of each regexp
   //
   foreach ($harsh_profanities as $profanity)
   {

      $profanity = trim($profanity);


      // for advanced filtering, insert possible punctuation
      // between each character in the target word
      //
      if ($advanced)
      {
         $pattern = '';
         for ($i = 0; $i < strlen($profanity); $i++)
            $pattern .= $profanity{$i}
                     . ($i < strlen($profanity) - 1 ? '[' . $punctuation . ']{0,}' : '');
      }
      else
         $pattern = $profanity;


      // is this profanity going to catch the replacement?
      // skip it if so
      //
      if (preg_match('/' . $pattern . '/i', $replacement))
         continue;


      // now just do the replacement
      //
      if ($match_profanity_length)
         $the_string = preg_replace('/' . $pattern . '/ie', 
                                    // I swear this should be \\1 and not \\0,
                                    // the PHP manual supports that "feeling",
                                    // but my development environment begs
                                    // to differ... ymmv
                                    'preg_replace("/./", $replacement, "\\0")', 
                                    $the_string);
      else
         $the_string = preg_replace('/' . $pattern . '/i', 
                                    $replacement, 
                                    $the_string);

   }


   return $the_string;

}



/**
  * Utility function that builds up a location string.
  *
  * Note that it always prepends & and thus won't work
  * for the first item in the query string.
  *
  * The variable will be renamed in the query string
  * if $queryName is given
  *
  * If the variable is empty, it will NOT be added to
  * the query string (blank string is returned).
  *
  * @param string $varName The name of the variable to push
  *                        into query string style
  * @param string $queryName The name of the variable to use
  *                          instead of the variable name from
  *                          which the value comes (optional;
  *                          default not used - same as var name).
  *
  * @return string Variable formatted in query string style
  *
  */
function censor_buildQueryString($varName, $queryName = '')
{
   global $$varName;
   ////if (isset($$varName))
   if (!empty($$varName))
      return '&' . (!empty($queryName) ? $queryName : $varName) . '=' . $$varName;
   else
      return '';
}



