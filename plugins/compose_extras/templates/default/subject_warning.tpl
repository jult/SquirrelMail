<?php

/**
  * subject_warning.tpl
  *
  * Template for outputting JavaScript that warns the user
  * when they have attempted to send a message without a subject.
  *
  * The following variables are available in this template:
  *
  * string  subject_warning_text  The text to be shown to the user
  *                               when submitting if the subject is empty
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
function check_subject()
{

   if (document.compose.subject.value != null
    && document.compose.subject.value != '')
   {

      // we could probably use the following, but the code below works
      // works with even older browsers (pre-version-4 generation)
      //
      //document.compose.subject.value = document.compose.subject.value.replace(/^\s+|\s+$/g,"");

      while (document.compose.subject.value.charAt(0) == ' '
          || document.compose.subject.value.charAt(0) == '\\n'
          || document.compose.subject.value.charAt(0) == '\\t'
          || document.compose.subject.value.charAt(0) == '\\f'
          || document.compose.subject.value.charAt(0) == '\\r')
         document.compose.subject.value = document.compose.subject.value.substring(1, document.compose.subject.value.length);

      while (document.compose.subject.value.charAt(document.compose.subject.value.length - 1) == ' '
          || document.compose.subject.value.charAt(document.compose.subject.value.length - 1) == '\\n'
          || document.compose.subject.value.charAt(document.compose.subject.value.length - 1) == '\\t'
          || document.compose.subject.value.charAt(document.compose.subject.value.length - 1) == '\\f'
          || document.compose.subject.value.charAt(document.compose.subject.value.length - 1) == '\\r')
         document.compose.subject.value = document.compose.subject.value.substring(0, document.compose.subject.value.length - 1);

   }


   if (document.compose.subject.value == null
    || document.compose.subject.value == '')
   {
      return confirm("<?php echo $subject_warning_text; ?>");
   }


   // always let the user through when subject was given
   //
   return true;


}
// -->
</script>

