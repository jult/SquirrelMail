<?php
 global $sent_conf_message_style, $sent_conf_include_recip_addr,
  $sent_conf_show_only_first_recip_addr, $sent_conf_allow_user_override,
  $emailAddressDelimiter, $sent_logo, $sent_logo_width, $sent_logo_height,
  $sent_conf_show_headers, $sent_conf_enable_orig_msg_options;

  $sent_conf_allow_user_override = 1;

   //   'off' :    No message shown
   //       1 :    "Message Sent" (above mailbox listing)
   //       2 :    "Your message has been sent"  (centered) 
   //              (above mailbox listing)
   //       3 :    Allow user to add addresses to address
   //              book (with optional image) (separate, 
   //              intermediary screen)
   //       4 :    "Message Sent To:"  (with user list and
   //              optional image) (separate, intermediary screen)
   //
 $sent_conf_message_style = '2';
   $sent_conf_include_recip_addr = 1;
   $sent_conf_show_only_first_recip_addr = 0;
   $sent_conf_include_cc = 1;
   $sent_conf_include_bcc = 1;
   $sent_logo = '';
   //$sent_logo = '../images/sm_logo.png';
   $sent_logo_width = '';
   $sent_logo_height = '';
   $sent_conf_show_headers = 1;
   $sent_conf_enable_orig_msg_options = 1;

   // the delimiter between account name and domain used in
   // email addresses on your system... it is rarely anything
   // except '@'
   //
   $emailAddressDelimiter = '@';

?>
