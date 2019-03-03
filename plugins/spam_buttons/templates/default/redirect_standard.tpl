<?php

/**
  * redirect_standard.tpl
  *
  * Template for returning to the message list (or next message)
  * for the Spam Buttons plugin.
  *
  * The following variables are available in this template:
  *
  * string  $redirect_location  The location to which we want to redirect
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
<script language="JavaScript" type="text/javascript">
<!--

document.location="<?php echo $redirect_location; ?>";

// -->
</script>

