<?php

/**
  * SquirrelMail Compose Extras Plugin
  *
  * Copyright (c) 2005-2012 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2003-2004 Justus Pendleton <justus@ryoohki.net>
  * Copyright (c) 2003 Bruce Richardson <itsbruce@uklinux.net>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage compose_extras
  *
  */

global $ce_limit_submit, $ce_disable_recipient_fields,
       $ce_default_subject_warning, $allow_acceses_keys,
       $ce_prevent_enter_causing_submit;



// Should the plugin attempt to limit the user's ability to click more
// than once to send an email?
//
//    0 = no
//    1 = yes
//
$ce_limit_submit = 1; 



// By default, should a blank subject cause a warning to
// pop up?  Users can change this setting, but this will
// serve as the default.  Consider user annoyance versus
// forgetfulness when choosing a value here.
//
//    0 = no
//    1 = yes
//
$ce_default_subject_warning = 0;



// Should users be forced to use the address book to add
// recipients?  If this is enabled, users won't be able to
// type anything directly into the To, Cc and Bcc fields
//
//    0 = no
//    1 = yes
//
$ce_disable_recipient_fields = 0;



// Should users be allowed to have access keys on the
// compose screen?
//
//    0 = no
//    1 = yes
//
$allow_acceses_keys = 1; 



// When pressing Enter in the To, Cc, Bcc and Subject
// text inputs, this usually causes the form to be
// submitted by the browser (simulates clicking on the
// first button, which is usually the Signature button).
// You can stop this from happening by enabling this
// setting.
//
//    0 = no, do not prevent
//    1 = yes, prevent form submit when Enter is pressed
//
$ce_prevent_enter_causing_submit = 1;



