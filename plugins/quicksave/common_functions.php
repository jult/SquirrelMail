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
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function quicksave_init()
{

   if (!@include_once (SM_PATH . 'config/config_quicksave.php'))
      if (!@include_once (SM_PATH . 'plugins/quicksave/config.php'))
         if (!@include_once (SM_PATH . 'plugins/quicksave/config_default.php'))
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
function quicksave_check_configuration_do()
{

   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically
   //
   if (!check_sm_version(1, 5, 2))
   {

      // try to find Compatibility, and then that it is v2.0.7+
      //
      if (function_exists('check_plugin_version')
       && check_plugin_version('compatibility', 2, 0, 7, TRUE))
         return FALSE;


      // something went wrong
      //
      do_err('Quick Save plugin requires the Compatibility plugin version 2.0.7+', FALSE);
      return TRUE;

   }

}



