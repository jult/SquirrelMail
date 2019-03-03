<?php

/**
  * SquirrelMail Variable Sent Folder Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002-2003 Robin Rainton <robin@rainton.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage variable_sent_folder
  *
  */



/**
  * Make sure this plugin runs after the sent_subfolders plugin
  * on the compose_send hook (loading_constants is not a good
  * place for this, but other choices aren't better: prefs_backend,
  * get_pref, abook_init
  *
  */
function variable_sent_folder_reorder_compose_send_hook()
{
   global $PHP_SELF;
   if (strpos($PHP_SELF, '/compose.php') === FALSE)
      return;

   reposition_plugin_on_hook('variable_sent_folder', 'compose_send', FALSE, 'sent_subfolders');
}



