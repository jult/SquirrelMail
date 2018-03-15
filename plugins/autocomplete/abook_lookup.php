<?php

/**
  * SquirrelMail Autocomplete Plugin
  *
  * Copyright (c) 2003-2012 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2005 Graham <gsm-smpi@soundclashchampions.com>
  * Copyright (c) 2001 Tyler Akins
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage autocomplete
  *
  * This script is intended as a RPC server.  It answers in JSON format.
  *
  * Test:
  * http://example.com/squirrelmail/plugins/autocomplete/abook_lookup.php?search=xxx
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
die('Sorry, Autocomplete is not compatible with SquirrelMail versions less than 1.4.0');
   chdir('..');
   define('SM_PATH', '../');
   include_once(SM_PATH . 'src/validate.php');
}


// Make sure plugin is activated!
//
global $plugins;
if (!in_array('autocomplete', $plugins))
   exit;



// disable browser caching
//
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: Sat, 1 Jan 2000 00:00:00 GMT');



// initialize addressbook
//
require_once(SM_PATH . 'functions/addressbook.php');
$abook = addressbook_init(FALSE, FALSE);



// get search terms
//
if (!sqgetGlobalVar('search', $search, SQ_GET))
{
   // no search given
   //
   echo '[]'; // empty JavaScript array
   exit;
}



// execute search
//
global $autocomplete_only_personal,
       $autocomplete_only_personal_allow_override,
       $squirrelmail_language;
include_once(SM_PATH . 'plugins/autocomplete/functions.php');
autocomplete_init();
if ($autocomplete_only_personal_allow_override)
{
   $autocomplete_only_personal = getPref($data_dir, $username,
                                 'autocomplete_only_personal',
                                 $autocomplete_only_personal);
}
if ($autocomplete_only_personal)
{
   $backend = $abook->localbackend;

   // hmmm, no personal address book?  this is a problem, 
   // but we'll let the admin figure it out elsewhere
   // 
   if ($backend == 0)
      $backend = -1;
}
else
   $backend = -1;
$search_result = $abook->s_search($search, $backend);



// error occurred
//
if (!is_array($search_result))
{

   // send back empty JavaScript array with error message in comments
   //
   echo '[] //ERROR: ' . $abook->error;
   exit;

}
else
{

   // no search results
   //
   if (sizeof($search_result) == 0)
   {
      echo '[]'; // empty JavaScript array
      exit;
   }

   // we have search results
   //
   else
   {
      $json = '';
      foreach ($search_result as $result)
      {
         $json .= '["'
                . ac_encode_javascript_string($result['nickname']) . '", "'
                . ac_encode_javascript_string(
                     ($squirrelmail_language == 'ja_JP'
                        ? $result['lastname'] . ' ' . $result['firstname']
                        : $result['name']))
                . '", "'
                . ac_encode_javascript_string($result['email']) . '"], ';
      }
      echo '[' . substr($json, 0, -2) . ']';
      exit;
   }

}



// should never get here, but to be safe...
//
echo '[]'; // empty JavaScript array
exit;



