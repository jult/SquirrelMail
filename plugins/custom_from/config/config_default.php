<?php

/**
  * SquirrelMail Custom From Plugin
  *
  * Copyright (c) 2003-2012 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage custom_from
  *
  */

global $restrict_access_to_users_file;


// This allows you to give access to the Custom From
// functionality to only a subset of your users.  When
// it is set to an empty string or invalid file name, 
// all users are able to use this plugin
//
// $restrict_access_to_users_file = '/path/to/squirrelmail/plugins/custom_from/config/custom_from_access_table.php';
//
$restrict_access_to_users_file = '';



