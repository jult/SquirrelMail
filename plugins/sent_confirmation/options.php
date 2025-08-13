<?php


/**
  * SquirrelMail Sent Confirmation Plugin
  * Copyright (C) 2004 Paul Lesneiwski <pdontthink@angrynerds.com>
  * This program is licensed under GPL. See COPYING for details
  *
  */


   global $sent_confirmation_debug;
   $sent_confirmation_debug = 0;

   // include compatibility plugin
   //
   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/compatibility/functions.php');
   else if (file_exists('../plugins/compatibility/functions.php'))
      include_once('../plugins/compatibility/functions.php');
   else if (file_exists('./plugins/compatibility/functions.php'))
      include_once('./plugins/compatibility/functions.php');



function sent_conf_options() {

   global $sent_conf_allow_user_override, $sent_conf_message_style,
          $sent_conf_include_recip_addr, $sent_conf_show_only_first_recip_addr,
          $username, $data_dir, $sent_conf_include_cc, $sent_conf_include_bcc,
          $sent_conf_show_headers, $sent_conf_enable_orig_msg_options, $sent_conf_enable;

   if (compatibility_check_sm_version(1, 3))
      include_once (SM_PATH . 'plugins/sent_confirmation/config.php');
   else
      include_once ('../plugins/sent_confirmation/config.php');

   if (!$sent_conf_allow_user_override)
      return;

   $sent_conf_enable = ($sent_conf_message_style == 'off' ? 0 : 1);
   $sent_conf_enable = getPref($data_dir, $username, 'sent_conf_enable', $sent_conf_enable);
   $sent_conf_style = getPref($data_dir, $username, 'sent_conf_style', $sent_conf_message_style);
   $sent_conf_incl_recip = getPref($data_dir, $username, 'sent_conf_incl_recip', $sent_conf_include_recip_addr);
   $sent_conf_show_only_first_recip_addr = getPref($data_dir, $username, 'sent_conf_show_only_first_recip_addr', $sent_conf_show_only_first_recip_addr);
   $sent_conf_include_cc = getPref($data_dir, $username, 'sent_conf_include_cc', $sent_conf_include_cc);
   $sent_conf_include_bcc = getPref($data_dir, $username, 'sent_conf_include_bcc', $sent_conf_include_bcc);
   $sent_conf_show_headers = getPref($data_dir, $username, 'sent_conf_show_headers', $sent_conf_show_headers);
   $sent_conf_enable_orig_msg_options = getPref($data_dir, $username, 'sent_conf_enable_orig_msg_options', $sent_conf_enable_orig_msg_options);


   bindtextdomain('sent_confirmation', SM_PATH . 'plugins/sent_confirmation/locale');
   textdomain('sent_confirmation');


   echo "<TR><TD COLSPAN=\"2\">&nbsp;</TD></TR>\n"
      . "<TR><TD ALIGN=CENTER VALIGN=MIDDLE COLSPAN=2 NOWRAP><B>"
      . _("Sent Mail Confirmation") . "</B></TD></TR>\n";

   echo "<tr><td align=right valign=top>\n".
      _("Confirm Recipients:") . "</td>\n".
      "<td><input type='radio' value='1' name='sent_conf_enable' ";
   if ($sent_conf_enable == '1') echo "CHECKED";
   echo ">&nbsp;" . _("Yes") . "\n".
      "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='sent_conf_enable' ";
   if ($sent_conf_enable == '0') echo "CHECKED";
   echo ">&nbsp;" . _("No") . "\n".
      "</td></tr>\n";

   echo "<tr><td align=right valign=top>\n".
      _("Above Message List:") . "</td>\n".
        "<td><input type='radio' value='1' name='sent_conf_style' ";
   if ($sent_conf_style == 1) echo "CHECKED";
   echo ">\n</td></tr>\n".
        "<tr><td align=right valign=top>\n".
      _("Above Message List, Expanded:") . "</td>\n".
        "<td><input type='radio' value='2' name='sent_conf_style' ";
   if ($sent_conf_style == 2) echo "CHECKED";
   echo ">\n</td></tr>\n".
        "<tr><td align=right valign=top>\n".
      _("Transitional Window,<br>With Address Book Functionality:") . "</td>\n".
        "<td><input type='radio' value='3' name='sent_conf_style' ";
   if ($sent_conf_style == 3) echo "CHECKED";
   echo ">\n</td></tr>\n".
        "<tr><td align=right valign=top>\n".
      _("Transitional Window, Simple:") . "</td>\n".
        "<td><input type='radio' value='4' name='sent_conf_style' ";
   if ($sent_conf_style == 4) echo "CHECKED";
   echo ">\n</td></tr>\n".

        "<tr><td align=right valign=top>\n".
      _("Show Header Fields:") . "</td>\n".
      "<td><input type='radio' value='1' name='sent_conf_show_headers' ";
   if ($sent_conf_show_headers == '1') echo "CHECKED";
   echo ">&nbsp;" . _("Yes") . "\n".
      "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='sent_conf_show_headers' ";
   if ($sent_conf_show_headers == '0') echo "CHECKED";
   echo ">&nbsp;" . _("No") . "\n".
      "</td></tr>\n";

   echo "<tr><td align=right valign=top>\n".
      _("Include Recipient Address:") . "</td>\n".
      "<td><input type='radio' value='1' name='sent_conf_incl_recip' ";
   if ($sent_conf_incl_recip == '1') echo "CHECKED";
   echo ">&nbsp;" . _("Yes") . "\n".
      "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='sent_conf_incl_recip' ";
   if ($sent_conf_incl_recip == '0') echo "CHECKED";
   echo ">&nbsp;" . _("No") . "\n".
      "</td></tr>\n";

   echo "<tr><td align=right valign=top>\n".
      _("Only Show First Recipient:") . "</td>\n".
      "<td><input type='radio' value='1' name='sent_conf_show_only_first_recip_addr' ";
   if ($sent_conf_show_only_first_recip_addr == '1') echo "CHECKED";
   echo ">&nbsp;" . _("Yes") . "\n".
      "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='sent_conf_show_only_first_recip_addr' ";
   if ($sent_conf_show_only_first_recip_addr == '0') echo "CHECKED";
   echo ">&nbsp;" . _("No") . "\n".
      "</td></tr>\n";

   echo "<tr><td align=right valign=top>\n".
      _("Show Cc Recipients:") . "</td>\n".
      "<td><input type='radio' value='1' name='sent_conf_include_cc' ";
   if ($sent_conf_include_cc == '1') echo "CHECKED";
   echo ">&nbsp;" . _("Yes") . "\n".
      "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='sent_conf_include_cc' ";
   if ($sent_conf_include_cc == '0') echo "CHECKED";
   echo ">&nbsp;" . _("No") . "\n".
      "</td></tr>\n";

   echo "<tr><td align=right valign=top>\n".
      _("Show Bcc Recipients:") . "</td>\n".
      "<td><input type='radio' value='1' name='sent_conf_include_bcc' ";
   if ($sent_conf_include_bcc == '1') echo "CHECKED";
   echo ">&nbsp;" . _("Yes") . "\n".
      "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='sent_conf_include_bcc' ";
   if ($sent_conf_include_bcc == '0') echo "CHECKED";
   echo ">&nbsp;" . _("No") . "\n".
      "</td></tr>\n";

   echo "<tr><td align=right valign=top>\n".
      _("Enable Original Message Options:") . "</td>\n".
      "<td><input type='radio' value='1' name='sent_conf_enable_orig_msg_options' ";
   if ($sent_conf_enable_orig_msg_options == '1') echo "CHECKED";
   echo ">&nbsp;" . _("Yes") . "\n".
      "&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='sent_conf_enable_orig_msg_options' ";
   if ($sent_conf_enable_orig_msg_options == '0') echo "CHECKED";
   echo ">&nbsp;" . _("No") . "\n".
      "</td></tr>\n";


   bindtextdomain('squirrelmail', SM_PATH . 'locale');
   textdomain('squirrelmail');

}



function sent_conf_options_save() {

   global $sent_conf_allow_user_override, $username, $data_dir,
          $sent_conf_style, $sent_conf_incl_recip, $sent_conf_include_cc,
          $sent_conf_show_only_first_recip_addr, $sent_conf_include_bcc,
          $sent_conf_show_headers, $sent_conf_enable_orig_msg_options,
          $sent_conf_enable;

   if (compatibility_check_sm_version(1, 3))
      include_once (SM_PATH . 'plugins/sent_confirmation/config.php');
   else
      include_once ('../plugins/sent_confirmation/config.php');

   if (!$sent_conf_allow_user_override)
      return;

   compatibility_sqextractGlobalVar('sent_conf_enable');
   compatibility_sqextractGlobalVar('sent_conf_style');
   compatibility_sqextractGlobalVar('sent_conf_incl_recip');
   compatibility_sqextractGlobalVar('sent_conf_show_only_first_recip_addr');
   compatibility_sqextractGlobalVar('sent_conf_include_cc');
   compatibility_sqextractGlobalVar('sent_conf_include_bcc');
   compatibility_sqextractGlobalVar('sent_conf_show_headers');
   compatibility_sqextractGlobalVar('sent_conf_enable_orig_msg_options');

   setPref($data_dir, $username, 'sent_conf_enable', $sent_conf_enable);
   setPref($data_dir, $username, 'sent_conf_style', $sent_conf_style);
   setPref($data_dir, $username, 'sent_conf_incl_recip', $sent_conf_incl_recip);
   setPref($data_dir, $username, 'sent_conf_show_only_first_recip_addr', $sent_conf_show_only_first_recip_addr);
   setPref($data_dir, $username, 'sent_conf_include_cc', $sent_conf_include_cc);
   setPref($data_dir, $username, 'sent_conf_include_bcc', $sent_conf_include_bcc);
   setPref($data_dir, $username, 'sent_conf_show_headers', $sent_conf_show_headers);
   setPref($data_dir, $username, 'sent_conf_enable_orig_msg_options', $sent_conf_enable_orig_msg_options);

}

?>
