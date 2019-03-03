<?php

/**
  * read_menubar_buttons.tpl
  *
  * Template for displaying spam/ham buttons in the button row
  * on the message view screen for the Spam Buttons plugin.
  *
  * The following variables are available in this template:
  *
  * array   $buttons        An array of buttons to be added, possibly
  *                         possibly one for the Spam button, one for 
  *                         the Ham button, or just one or none
  * int     $startMessage   The message index that indicates the 
  *                         current message list page from which
  *                         this message was viewed.
  * boolean $view_as_html   Whether or not the message is being viewed
  *                         in HTML mode
  * int     $account        The account index
  * string  $passed_ent_id  The entity ID in the parent message, if any
  * string  $passed_id      The message ID of the message being viewed
  * string  $mailbox        The current mailbox (where this message is found)
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


// if no buttons to be displayed, no output
//
if (empty($buttons))
   return;


?>
&nbsp;&nbsp;|&nbsp;

<form method="post" style="display: inline">

<input type="hidden" name="mailbox" value="<?php echo $mailbox; ?>" />
<input type="hidden" name="msg[0]" value="<?php echo $passed_id; ?>" />
<input type="hidden" name="passed_ent_id" value="<?php echo $passed_ent_id; ?>" />
<input type="hidden" name="account" value="<?php echo $account; ?>" />
<input type="hidden" name="view_as_html" value="<?php echo $view_as_html; ?>" />
<input type="hidden" name="startMessage" value="<?php echo $startMessage; ?>" />

<small>

<?php

   foreach ($buttons as $name => $button)
   {

      echo '&nbsp;<input name="' . $name . '" type="' . $button['type'] . '" value="' . $button['value'] . '" />';

   }
?>

</small>

</form>




