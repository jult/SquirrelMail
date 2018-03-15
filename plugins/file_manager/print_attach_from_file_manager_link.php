<?php


function print_attach_from_file_manager_link()
{

   global $plugins, $color;

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/file_manager/functions.php');
   else
      include_once('../plugins/file_manager/functions.php');


   // get global variables for versions of PHP < 4.1
   //
   if (!compatibility_check_php_version(4, 1)) {
      global $HTTP_SESSION_VARS, $HTTP_SERVER_VARS;
      $_SESSION = $HTTP_SESSION_VARS;
      $_SERVER = $HTTP_SERVER_VARS;
   }


   global $file_manager_config;
   getFileManagerUserConfig();



   // users without permissions get booted
   //
   if (!in_array($_SESSION['username'], array_keys($file_manager_config)))
      return;



   // when we go to get a file, gotta save all the fields from this mail
   // so they will be here when we get back...
   //
   bindtextdomain('file_manager', '../plugins/file_manager/locale');
   textdomain('file_manager');

   echo '<TABLE ALIGN=CENTER BORDER=0 CELLSPACING=0 CELLPADDING=4 WIDTH="100%"><TR><TD BGCOLOR="'
      . $color[0] . '">'
      . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
      . '<small><a href="" onClick="';

   if (in_array('quicksave', $plugins))
      echo 'QuickSave_activate(false); ';

   echo 'if (document.compose.body) document.fileManagerForm.body.value=document.compose.body.value; '
      . 'if (document.compose.delete_draft) document.fileManagerForm.delete_draft.value=document.compose.delete_draft.value; '
      . 'if (document.compose.identity) document.fileManagerForm.identity.value=document.compose.identity.value; '
      . 'if (document.compose.variable_sent_folder) document.fileManagerForm.variable_sent_folder.value=document.compose.variable_sent_folder.value; '
      . 'if (document.compose.mailprio) document.fileManagerForm.mailprio.value=document.compose.mailprio.value; '
      . 'if (document.compose.session) document.fileManagerForm.session.value=document.compose.session.value; '
      . 'if (document.compose.action) document.fileManagerForm.action.value=document.compose.action.value; '
      . 'if (document.compose.forward_id) document.fileManagerForm.forward_id.value=document.compose.forward_id.value; '
      . 'if (document.compose.reply_id) document.fileManagerForm.reply_id.value=document.compose.reply_id.value; '
      . 'if (document.compose.request_mdn && document.compose.request_mdn.checked) document.fileManagerForm.request_mdn.value=document.compose.request_mdn.value; '
      . 'if (document.compose.request_dr && document.compose.request_dr.checked) document.fileManagerForm.request_dr.value=document.compose.request_dr.value; '
      . 'if (document.compose.send_to) document.fileManagerForm.fm_send_to.value=document.compose.send_to.value; '
      . 'if (document.compose.send_to_cc) document.fileManagerForm.fm_send_to_cc.value=document.compose.send_to_cc.value; '
      . 'if (document.compose.send_to_bcc) document.fileManagerForm.fm_send_to_bcc.value=document.compose.send_to_bcc.value; '
      . 'if (document.compose.subject) document.fileManagerForm.subject.value=document.compose.subject.value; '
      . 'if (document.compose.passed_id) document.fileManagerForm.passed_id.value=document.compose.passed_id.value; '
      . 'if (document.compose.mailbox) document.fileManagerForm.mailbox.value=document.compose.mailbox.value; '

      . 'document.fileManagerForm.submit(); return false;">'

      . _("Add Attachment From File Manager") . '</a></small>'

      . '</TD></TR></TABLE>'

      . '<FORM name="fileManagerForm" action="../plugins/file_manager/file_manager.php" method="POST">'
      . '<INPUT type="hidden" name="body">'
      . '<INPUT type="hidden" name="delete_draft">'
      . '<INPUT type="hidden" name="identity">'
      . '<INPUT type="hidden" name="variable_sent_folder">'
      . '<INPUT type="hidden" name="mailprio">'
      . '<INPUT type="hidden" name="session">'
      . '<INPUT type="hidden" name="action">'
      . '<INPUT type="hidden" name="forward_id">'
      . '<INPUT type="hidden" name="reply_id">'
      . '<INPUT type="hidden" name="request_mdn">'
      . '<INPUT type="hidden" name="request_dr">'
      . '<INPUT type="hidden" name="fm_send_to">'
      . '<INPUT type="hidden" name="fm_send_to_cc">'
      . '<INPUT type="hidden" name="fm_send_to_bcc">'
      . '<INPUT type="hidden" name="subject">'
      . '<INPUT type="hidden" name="passed_id">'
      . '<INPUT type="hidden" name="mailbox">'
      . '</FORM>';

   bindtextdomain('squirrelmail', '../locale');
   textdomain('squirrelmail');

}


?>
