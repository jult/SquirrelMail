<?php

/**
  * redirect_preview_pane.tpl
  *
  * Template for closing the current message view (or moving it
  * to the next message) and refreshing the message list for the 
  * Spam Buttons plugin.
  *
  * The following variables are available in this template:
  *
  * string  $redirect_location  The target location to which the
  *                             message view should be redirected
  * boolean $request_refresh_message_list When TRUE, indicate to the page
  *                                       to which we are redirecting that
  *                                       we'd like it to refresh the message
  *                                       list after it loads
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


// add message list refresh request if needed
//
if ($request_refresh_message_list)
{
   if (strpos($redirect_location, '?') === FALSE)
      $redirect_location .= '?pp_rr_force=1';
   else
      $redirect_location .= '&pp_rr_force=1';
}


?> 
<script language="JavaScript" type="text/javascript">
<!--

document.location="<?php echo $redirect_location; ?>";

// -->
</script>

