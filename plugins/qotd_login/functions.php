<?php

/**
  * SquirrelMail Quote of the Day at Login Plugin
  *
  * Copyright (c) 2003-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002 Tracy McKibben <tracy@mckibben.d2g.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage qotd_login
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
function qotd_login_init($debug=FALSE)
{

   if ($debug)
   {
      if (!include_once(SM_PATH . 'config/config_qotd_login.php'))
         if (!include_once(SM_PATH . 'plugins/qotd_login/config.php'))
            if (!include_once(SM_PATH . 'plugins/qotd_login/config_default.php'))
               return FALSE;
   }
   else
   {
      if (!@include_once(SM_PATH . 'config/config_qotd_login.php'))
         if (!@include_once(SM_PATH . 'plugins/qotd_login/config.php'))
            if (!@include_once(SM_PATH . 'plugins/qotd_login/config_default.php'))
               return FALSE;
   }


   return TRUE;

}



/**
  * Show quote on login page
  *
  */
function qotd_login_get_quote() 
{

   global $fortune_command, $qotdSource, $qotd_org_address,
          $qotd_org_cache_seconds, $qotd_login_debug;
   qotd_login_init();


   // autodetect qotd source - if fortune program
   // isn't available, only pull from qotd.org
   //
   if ($qotdSource == 0)
   {
      // parses just the program path out of the command text
      //
      preg_match('/(.+?)(\s|$)/', $fortune_command, $matches);

      // check that it is valid and executable
      // 
      if (!is_executable($matches[1]))
         $qotdSource = 1;
   }


   // initialize random number generator
   //
   if (!check_sm_version(1, 5, 2))
      sq_mt_randomize();


   //
   // figure out where to pull the quote from and do it...
   //


   // use qotd.org
   //
   if ($qotdSource == 1 || ($qotdSource == 0 && mt_rand(1, 20) > 10))
   {

      global $prefs_dsn, $data_dir, $prefs_are_cached, $prefs_cache;

      // eiw - a bit messy, but we need to have prefs
      // functions here on the login page where they
      // are not usually needed - borrowed from init.php
      // and NOT tested with custom pref backends
      //
      if (check_sm_version(1, 5, 2))
      {
/* more recently, 1.5.2 fixed this issue......
         include_once(SM_PATH . 'functions/strings.php');
         include_once(SM_PATH . 'functions/prefs.php');
         $prefs_backend = do_hook('prefs_backend', $null);
         if (isset($prefs_backend) && !empty($prefs_backend) && file_exists(SM_PATH . $prefs_backend)) {
             include_once(SM_PATH . $prefs_backend);
         } elseif (isset($prefs_dsn) && !empty($prefs_dsn)) {
             include_once(SM_PATH . 'functions/db_prefs.php');
         } else {
             include_once(SM_PATH . 'functions/file_prefs.php');
         }
*/
      }
      else
         include_once(SM_PATH . 'functions/prefs.php');


      // check quote cache
      //
      // note how we unset the prefs cache flag before/after this
      // code so as not to corrupt any prefs for the user who is
      // currently logging in
      //
      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');

      $quote = getPref($data_dir, 'qotd_login', 'quote', '');
      $quote_date = getPref($data_dir, 'qotd_login', 'quote_date', '');

      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');


      // if cache is too old (or is empty), go get quote from qotd.org
      //
      $now = time();
      if (empty($quote) || empty($quote_date)
       || (!empty($quote_date) && $now - $quote_date > $qotd_org_cache_seconds))
      {

         // get qotd
         //
         $QOTD = sq_call_function_suppress_errors('fopen', array($qotd_org_address, 'r'));
         if ($QOTD !== FALSE)
         {

            $quote = '';
            while (!feof($QOTD))
               $quote .= fgets($QOTD, 1024);  // why was this 80? that should be too small for most quotes and thus create multiple unnecessary reads
            fclose($QOTD);


            // parse quote out of web page
            //
            $explode = explode('<br />', $quote, 2);
            $quote = $explode[1];
            $explode = explode('</p>', $quote, 2);
            $quote = $explode[0];
/* ----------------------------------------
            // this seems to be how it used to work for deprecated URI
            //
            $explode = explode('<font size="-1">', $quote, 2);
            $quote = $explode[1];
            $explode = explode("</font>", $quote, 2);
            $quote = $explode[0];
            $quote = str_replace('A HREF', 'A TARGET="_blank" HREF', $quote);
            $quote = str_replace('A randomly selected quote', 'Courtesy of <a href="http://www.qotd.org/" target="_blank">qotd.org</a>', $quote);
            $quote = str_replace('http://www.qotd.org/env.gif', sqm_baseuri() . 'plugins/qotd_login/images/env.gif', $quote);
---------------------------------------- */
/* ----------------------------------------
            // this is how to parse it from the main web site
            //
            $explode = explode('</em><br />', $quote, 2);
            $quote = $explode[1];
            $explode = explode('</p>', $quote, 2);
            $quote = $explode[0];
            $explode = explode('<a href="/search/search.html?aid=', $quote, 2);
            $quote = $explode[0];
            $explode = explode('">', $explode[1], 2);
            $quote .= str_replace('</a>', '', $explode[1]);
---------------------------------------- */


            // cache the quote and current time
            //
            if ($qotd_org_cache_seconds)
            {
               $quote = str_replace(array("\r", "\n"), '', $quote); // file prefs system can't handle CR or LF in preference values
               setPref($data_dir, 'qotd_login', 'quote', $quote);
               setPref($data_dir, 'qotd_login', 'quote_date', $now);

               // clear prefs cache again
               //
               $prefs_are_cached = FALSE;
               $prefs_cache = FALSE;
               sqsession_unregister('prefs_are_cached');
               sqsession_unregister('prefs_cache');
            }

         }
         else
         {
            if ($qotd_login_debug)
            {
               global $color;
               $ret = plain_error_message('Error connecting to ' . $qotd_org_address, $color);
               if (check_sm_version (1, 5, 2))
               {
                  echo $ret;
                  global $oTemplate;
                  $oTemplate->display('footer.tpl');
               }
               exit;
            }
         }

      }

   }


   // get a quote from the fortune program
   //
   else
   {
      $quote = shell_exec($fortune_command);
      //$quote = nl2br($quote);
   }


   // now send the quote to the interface...
   //
   // 1.5.2+ uses templated output
   //
   if (check_sm_version(1, 5, 2))
   {
      global $oTemplate;
      $oTemplate->assign('quote', $quote, FALSE);  // FALSE here allows HTML in the quote, but assumes we can trust that it is clean of possible Bad Stuff
      $oTemplate->display('plugins/qotd_login/quote.tpl');
   }


   // 1.4.x
   //
   else
   {
      echo '<br /><center><img src="'
         . sqm_baseuri() . 'plugins/qotd_login/images/qotd.gif"><br /><br />'
         . $quote
         . "</center>";
   }

}



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function qotd_login_check_configuration()
{

   // make sure plugin is correctly configured
   //
   global $fortune_command, $qotdSource, $qotd_org_address;
   if (!qotd_login_init())
   {
      do_err('Quote of the Day at Login plugin is not configured correctly', FALSE);
      return TRUE;
   }


   // if qotd source is set to autodetect or use the
   // fortune command, check that the fortune program
   // isn't missing or broken
   //
   if ($qotdSource == 0 || $qotdSource == 2)
   {

      // parses just the program path out of the command text
      //
      preg_match('/(.+?)(\s|$)/', $fortune_command, $matches);

      // check that it is valid and executable
      //
      if (!is_executable($matches[1]))
      {
         if (file_exists($matches[1]))
            do_err('The Quote of the Day at Login plugin is set to use the fortune program as "' . $fortune_command . '", but "' . $matches[1] . '" is not executable by the web server', FALSE);
         else
            do_err('The Quote of the Day at Login plugin is set to use the fortune program as "' . $fortune_command . '", but "' . $matches[1] . '" does not exist', FALSE);

         return TRUE;
      }

   }


   // if qotd source is set to autodetect or pull from
   // qotd.org, check that qotd.org is available
   //
   if ($qotdSource == 0 || $qotdSource == 1)
   {
      $QOTD = fopen($qotd_org_address, 'r');
      if (!$QOTD)
      {
         do_err('The Quote of the Day at Login plugin is set to retrieve qutoes from "' . $qotd_org_address . '", but I could not connect to that address', FALSE);
         return TRUE;
      }
      fclose($QOTD);
   }


   // everything's OK
   //
   return FALSE;

}



