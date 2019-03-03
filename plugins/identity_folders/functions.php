<?php

/**
  * SquirrelMail Identity Folders Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage identity_folders
  *
  */



/**
  * Override custom sent folder with the folder that
  * corresponds to the current identity, if any
  * (requires Variable Sent Folder plugin)
  *
  * If a user setting for it is found, the corresponding
  * IMAP folder name is placed in the global variable
  * $variable_sent_folder_custom_sent_folder_default
  *
  */
function if_custom_sent_folder($args)
{
   global $username, $data_dir, $identity,
          $variable_sent_folder_custom_sent_folder_default;

   // if someone else already set a custom sent folder,
   // ours isn't as important, so just bail
   //
   if (!empty($variable_sent_folder_custom_sent_folder_default))
      return;

   $identity_folder = getPref($data_dir, $username, 'identity_folder_' . $identity, SMPREF_NONE);
   if ($identity_folder === SMPREF_NONE)
      return;

   $variable_sent_folder_custom_sent_folder_default = $identity_folder;
}



