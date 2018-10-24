<?php

/**
  * SquirrelMail Per Recipient Sent Folders Plugin
  *
  * Copyright (c) 2013-2014 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage per_recipient_sent_folders
  *
  */



global $prsf_detect_upon_send_default,
       $prsf_use_previous_multi_recipient_sent_folder_for_single_recipient_default;



// Should the sent folder drop-down selection (requires
// the Variable Sent Folder plugin) be associated with
// the recipients in each outgoing message?
//
// 1 = yes
// 0 = no
//
// This is a default value; users can override it in their preferences
//
$prsf_detect_upon_send_default = 0;



// When looking for a matching sent folder, should
// single recipients be matched with previous folders
// that were used when sending a multiple-recipient
// message (one of which was the single recipient)?
//
// 1 = yes
// 0 = no
//
// This is a default value; users can override it in their preferences
//
$prsf_use_previous_multi_recipient_sent_folder_for_single_recipient_default = 1;



