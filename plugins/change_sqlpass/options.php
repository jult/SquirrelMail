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


if (!defined('SM_PATH')) define('SM_PATH', '../../');



if (file_exists(SM_PATH . 'include/validate.php'))
   include_once(SM_PATH . 'include/validate.php');
else
   include_once(SM_PATH . 'src/validate.php');



load_config('change_sqlpass', array('config.php'));
include_once(SM_PATH . 'plugins/change_sqlpass/functions.php');



// validate and process a password submission
//
if (sqgetGlobalVar('csp_submit_change', $csp_submit_change, SQ_FORM))
   $messages = process_password_change_request();



// need to possibly change to HTTPS when first coming here
//
else
{

   csp_redirect_to_ssl_connection();
   $messages = array();

}



// build the screen
//
global $color;
displayPageHeader($color, 'None');
show_change_pwd_screen($messages);
echo '</body></html>';
exit;



?>
