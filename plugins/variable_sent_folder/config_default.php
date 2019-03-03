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

global $variable_sent_folder_default_status, $variable_sent_folder_default_folder;


// By default, should the custom sent folder
// selector be enabled?
//
//    0 = No, let users turn it on if they want it
//    1 = Yes, but only when replying to messages
//    2 = Yes, but only when composing new messages
//    3 = Yes, it should always be enabled
//
$variable_sent_folder_default_status = 0;



// When the custom sent folder selector is being shown,
// what should the default selection be?
//
//    1 = The normal sent folder (as defined in the user's folder preferences settings)
//    2 = The current folder that is being viewed
//
$variable_sent_folder_default_folder = 1;



