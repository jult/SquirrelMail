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
  */


/**
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function autocomplete_init()
{

   if (!@include_once (SM_PATH . 'config/config_autocomplete.php'))
      if (!@include_once (SM_PATH . 'plugins/autocomplete/config.php'))
         if (!@include_once (SM_PATH . 'plugins/autocomplete/config_default.php'))
            return FALSE;

   return TRUE;

/* ----------  This is how to do the same thing using the Compatibility plugin
   return load_config('autocomplete',
                      array('../../config/config_autocomplete.php',
                            'config.php',
                            'config_default.php'),
                      TRUE, TRUE);
----------- */

}



/**
  * Encodes a string bound for a double-quoted JavaScript expression
  *
  * @param string $string The string to be encoded
  *
  * @return string The encoded string
  *
  */
function ac_encode_javascript_string($string)
{
   return str_replace(array('\\', '"'), array('\\\\', '\\"'), $string);
}



