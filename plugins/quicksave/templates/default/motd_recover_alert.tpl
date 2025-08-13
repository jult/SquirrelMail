<?php

/**
  * motd_recover_alert.tpl
  *
  * Template for building a message recovery alert to be 
  * shown in the MOTD area for the Quick Save plugin
  *
  * The following variables are available in this template:
  *
  * boolean $show_message_details_in_motd Whether or not details about 
  *                                       the recovered message should
  *                                       be displayed.
  * boolean $motd_pad             Whether or not MOTD already has something 
  *                               in it and should be padded before adding 
  *                               our output
  * string  $compose_uri          The full compose hyperlink
  * string  $send_to_contents     The pre-formatted (length-trimmed) To: 
  *                               header contents
  * string  $send_to_cc_contents  The pre-formatted (length-trimmed) Cc:
  *                               header contents
  * string  $send_to_bcc_contents The pre-formatted (length-trimmed) Bcc:
  *                               header contents
  * string  $subject_contents     The pre-formatted (length-trimmed) Subject:
  *                               header contents
  * string  $body_contents        The pre-formatted (length-trimmed) body 
  *                               contents (might be empty)
  *
  * @copyright &copy; 1999-2010 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package squirrelmail
  * @subpackage plugins
  */


// retrieve the template vars
//
extract($t);


if ($motd_pad)
   echo '<br /><br />';

if ($show_message_details_in_motd)
   echo '<div align="center">'
      . _("NOTE: The following email was interrupted and was never sent:")
      . '<br />&nbsp;&nbsp;'
      . _("To:") . '&nbsp;'
      . $send_to_contents
      . '<br />&nbsp;&nbsp;'
      . _("Subject:") . '&nbsp;'
      . $subject_contents
      . (!empty($body_contents) ? '<br />' : '')
      . $body_contents
      . '<br />'
      . $compose_uri
      . '</div>';
else
   echo '<div align="center">'
      . _("NOTE: You have an unsent message that was interrupted.")
      . '<br />'
      . $compose_uri
      . '</div>';

