<?php

/**
  * SquirrelMail Multiple Attachments Plugin
  *
  * Copyright (c) 2012-2012 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage multiple_attachments
  *
  */

global $number_of_attachment_inputs, $allow_dynamic_input_addition,
       $number_of_attachment_inputs_allow_override;



// How many attachment inputs should be shown by default?
//
$number_of_attachment_inputs = 1; 



// Should users be allowed to set their own number of default
// attachment inputs?
//
//    0 = no
//    1 = yes
//
$number_of_attachment_inputs_allow_override = 0;



// Should users be allowed to add more upload inputs on the fly?
//
// Note that this feature is automatically disabled when the
// browser (or user preferences) do not support JavaScript.
//
//    0 = no
//    1 = yes
//
$allow_dynamic_input_addition = 1;



