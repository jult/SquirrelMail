<?php

/**
  * confirmation_note.tpl
  *
  * Template for displaying a spam/ham report message on
  * the message view screen for the Spam Buttons plugin.
  *
  * The following variables are available in this template:
  *
  * string  $note  The report confirmation text to be displayed
  *
  * Copyright (c) 2005-2009 Paul Lesniewski <paul@squirrelmail.org>,
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage spam_buttons
  *
  */


// retrieve the template vars
//
extract($t);


?>
<tr><td colspan="2" align="center"><strong><?php echo $note; ?></strong></td></tr>

