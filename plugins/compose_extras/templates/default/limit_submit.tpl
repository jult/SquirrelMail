<?php

/**
  * limit_submit.tpl
  *
  * Template for outputting JavaScript that prevents the user
  * from pressing the Submit button more than once per send.
  *
  * The following variables are available in this template:
  *
  * string  warning_text  The text to be shown to the user
  *                       when a subsequent click is detected
  *
  * @copyright &copy; 1999-2012 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package squirrelmail
  * @subpackage plugins
  */


// retrieve the template vars
//
extract($t);


?>
<script language="JavaScript" type="text/javascript">
<!--
var submit_count=0;
function submitOnlyOnce() {
   if (submit_count == 0) {
      submit_count++;
      return true;
   } else {
      alert("<?php echo $warning_text; ?>");
      return false;
   }
}
// -->
</script>

