<?php
   /*
    *  Undelete message plugin
    *  By Nick Sayer <nsayer@quack.kfu.com>
    *  (c) 2001 (GNU GPL - see ../../COPYING)
    *
    *  This plugin adds an 'undelete' button to the mailbox message list.
    *  Pretty self explanatory. It looks like the code for undelete was once
    *  part of squirrelmail, but while the button was inexplicably removed
    *  the handler for it remains, at least as of 1.0.5.
    *
    *  This is a plugin that should go away, since they really should just
    *  put the undelete button back for people who don't have either move-to-
    *  trash or autoexpunge turned on.
    *
    *  If at some point they get rid of the undelete handler (which means
    *  this plugin will stop working), then it will simply have to be made
    *  slightly more complex -- it will have to generate its own <FORM>,
    *  with a handler URL (hook?) to do the undelete and return you to
    *  right_main.
    *
    */

   function squirrelmail_plugin_init_undelete() {
      global $squirrelmail_plugin_hooks;

      $squirrelmail_plugin_hooks["mailbox_form_before"]["undelete"] = "offer_undelete";
   }

   function offer_undelete() {
      global $color;
      print("<TR><TD BGCOLOR=\"$color[0]\" ALIGN=RIGHT><small>".
         "<input type=SUBMIT value=\"Undelete\" name=\"undeleteButton\"> ".
         _("checked messages")."</small>".
         "</td></tr>\n");
   }
?>
