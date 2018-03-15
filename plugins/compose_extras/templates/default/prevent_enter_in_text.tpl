<?php

/**
  * prevent_enter_in_text.tpl
  *
  * Template for outputting JavaScript that prevents form
  * submit from happening when the user presses Enter in
  * the To, Cc, Bcc and Subject fields on the compose screen.
  *
  * The following variables are available in this template:
  *
  * <none>
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


// NB: We could apply this to each text input as follows, but because
//     other plugins (such as Auto Complete) might want to fiddle with
//     the same event on some of those inputs, we'll back off to the
//     form's onkeypress instead (see bottom of script below)
//
//   document.compose.send_to.onkeypress=prevent_submit_on_enter;
//   document.compose.send_to_cc.onkeypress=prevent_submit_on_enter;
//   document.compose.send_to_bcc.onkeypress=prevent_submit_on_enter;
//   document.compose.subject.onkeypress=prevent_submit_on_enter;


?>
<script language="JavaScript" type="text/javascript">
<!--
   function prevent_submit_on_enter_in_text_input(e)
   {
      var event = e || window.event;
      if (typeof(event) != 'undefined')
      {
         var target = event.target || event.srcElement;
         // Safari bug...
         if (target && target.nodeType && target.nodeType == 3) target = target.parentNode;
         if (target && target.type && target.type == 'text')
         {
            var key = event.keyCode || event.which || event.charCode;
            if (key == 13)
            {
               event.cancelBubble = true;
               event.returnValue = true;
               if (event.stopPropagation)
               {
                  event.stopPropagation();
                  event.preventDefault();
               }
               return false;
            }
         }
      }
      return true;
   }
   document.compose.onkeypress=prevent_submit_on_enter_in_text_input;
// -->
</script>

