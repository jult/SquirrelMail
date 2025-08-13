<?php

/**
  * disable_recipients.tpl
  *
  * Template for outputting JavaScript that prevents the user
  * from entering anything into the To, Cc and Bcc text inputs
  * on the compose page.
  *
  * The following variables are available in this template:
  *
  * <none>
  *
  * @copyright &copy; 2003-2012 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package squirrelmail
  * @subpackage plugins
  */


?>
<script language="JavaScript" type="text/javascript">
<!--
   document.compose.send_to.readOnly = true;
   document.compose.send_to_cc.readOnly = true;
   document.compose.send_to_bcc.readOnly = true;
// -->
</script>

