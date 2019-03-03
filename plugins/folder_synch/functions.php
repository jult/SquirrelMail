<?php

/**
  * SquirrelMail Folder Synchronization Plugin
  *
  * Copyright (c) 2011-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2003 Nick Bartos <>
  * Copyright (c) 2002 Jimmy Conner <jimmy@advcs.org>
  * Copyright (c) 2002 Jay Guerette <JayGuerette@pobox.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage folder_synch
  *
  */



/**
  * Initialize this plugin (load config values)
  *
  * @param boolean $debug When TRUE, do not suppress errors when including
  *                       configuration files (OPTIONAL; default FALSE)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function folder_synch_init($debug=FALSE)
{

   if ($debug)
   {
      if (!include_once(SM_PATH . 'config/config_folder_synch.php'))
         if (!include_once(SM_PATH . 'plugins/folder_synch/config.php'))
            if (!include_once(SM_PATH . 'plugins/folder_synch/config_default.php'))
               return FALSE;
   }
   else
   {
      if (!@include_once(SM_PATH . 'config/config_folder_synch.php'))
         if (!@include_once(SM_PATH . 'plugins/folder_synch/config.php'))
            if (!@include_once(SM_PATH . 'plugins/folder_synch/config_default.php'))
               return FALSE;
   }


   return TRUE;

}



/**
  * Show options on folder preferences page
  *
  */
function folder_synch_display_options()
{

   global $javascript_on;
   if (!$javascript_on) return;

   global $data_dir, $username, $optpage_data, 
          $default_folder_list_resynch, $allow_override_folder_list_resynch,
          $default_message_list_resynch, $allow_override_message_list_resynch;

   folder_synch_init();

   sq_change_text_domain('folder_synch');

   $my_optpage_values = array();


   if ($allow_override_message_list_resynch)
   {
      $message_list_resynch = getPref($data_dir, $username,
                                      'folder_synch_opt_right',
                                      $default_message_list_resynch);
      $my_optpage_values[] = array(
         'name'          => 'folder_synch_opt_right',
         'caption'       => _("Refresh message list when folder status changes"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $message_list_resynch,
         'refresh'       => SMOPT_REFRESH_FOLDERLIST,
      );
   }


   if ($allow_override_folder_list_resynch)
   {
      $folder_list_resynch = getPref($data_dir, $username,
                                     'folder_synch_opt_left',
                                     $default_folder_list_resynch);
      $my_optpage_values[] = array(
         'name'          => 'folder_synch_opt_left',
         'caption'       => _("Refresh folder list upon message list actions"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $folder_list_resynch,
         'refresh'       => SMOPT_REFRESH_FOLDERLIST,
      );
   }


   if (!empty($my_optpage_values))
      $optpage_data['vals'][1] = array_merge($optpage_data['vals'][1], $my_optpage_values);
        

   sq_change_text_domain('squirrelmail');

}



/** 
  * Insert needed script on folder list page
  *
  */
function folder_synch_insert_folder_list_script()
{

   global $javascript_on;
   if (!$javascript_on) return;

   global $data_dir, $username, $imapConnection, $boxes,
          $default_folder_list_resynch, $allow_override_folder_list_resynch,
          $default_message_list_resynch, $allow_override_message_list_resynch;

   folder_synch_init();


   $message_list_resynch = $default_message_list_resynch;
   if ($allow_override_message_list_resynch)
      $message_list_resynch = getPref($data_dir, $username,
                                      'folder_synch_opt_right',
                                      $default_message_list_resynch);


   $folder_list_resynch = $default_folder_list_resynch;
   if ($allow_override_folder_list_resynch)
      $folder_list_resynch = getPref($data_dir, $username,
                                     'folder_synch_opt_left',
                                     $default_folder_list_resynch);


   if (!$message_list_resynch && !$folder_list_resynch)
      return;


   // either of the refresh/synchronization options require some shared script
   //
   if (empty($boxes))
      $boxes = sqimap_mailbox_list($imapConnection);

   $script = "<script type=\"text/javascript\" language=\"JavaScript\">\n<!--\n"
           . 'var mailbox_safe_names = new Array();'
           . 'var mailbox_message_counts = new Array();'
           . 'var mailbox_unread_counts = new Array();';

   foreach ($boxes as $box)
   {
      $box_status = sqimap_status_messages($imapConnection, $box['unformatted']);
      $message_count = (empty($box_status['MESSAGES']) ? 0 : $box_status['MESSAGES']);
      $unseen_count = (empty($box_status['UNSEEN']) ? 0 : $box_status['UNSEEN']);
      $script .= 'mailbox_safe_names["' . $box['unformatted'] . '"] = "' . preg_replace("/[^0-9A-Za-z_]/", '_', $box['unformatted'])  . '";'
               . 'mailbox_message_counts["' . $box['unformatted'] . '"] = ' . $message_count . ';'
               . 'mailbox_unread_counts["' . $box['unformatted'] . '"] = ' . $unseen_count . ';';
   }


   // Force message list resynch upon folder list change/update?
   //
   // Add script that compares current message list status with
   // what we have here in the folder list and initiates refresh
   // if they are different.
   //
   // If the message list is not showing, this will do nothing
   //
   if ($message_list_resynch)
   {
      $script .= 'if (parent'
               . ' && parent.right'
               . ' && typeof parent.right.total_message_count != "undefined"'
               . ' && typeof parent.right.unread_message_count != "undefined"'
               . ' && typeof parent.right.mailbox_name != "undefined"'
               . ' && parent.right.mailbox_name in mailbox_message_counts'
               . ' && parent.right.mailbox_name in mailbox_unread_counts'
               . ' && (mailbox_message_counts[parent.right.mailbox_name] != parent.right.total_message_count'
               . ' || mailbox_unread_counts[parent.right.mailbox_name] != parent.right.unread_message_count)) {'
               . 'var preselected = "";'
               . 'if (parent.right.document.forms["FormMsgs" + mailbox_safe_names[parent.right.mailbox_name]]) {'
               . 'for (i = 0; i < parent.right.document.forms["FormMsgs" + mailbox_safe_names[parent.right.mailbox_name]].length; i++)'
               . 'if (parent.right.document.forms["FormMsgs" + mailbox_safe_names[parent.right.mailbox_name]].elements[i].type == "checkbox" && parent.right.document.forms["FormMsgs" + mailbox_safe_names[parent.right.mailbox_name]].elements[i].checked) {'
               . 'preselected += "preselected[" + parent.right.document.forms["FormMsgs" + mailbox_safe_names[parent.right.mailbox_name]].elements[i].value + "]=1&";'
               . '}'
               . '}'
               . 'if (preselected == "")'
               . 'parent.right.location.reload(true);'
               . 'else {'
               . 'var addr = parent.right.location.href;'
               . 'if (addr.indexOf("?") != -1) parent.right.location.replace(addr.replace(/(&|\?)preselected\[\d+\]=1/g, "") + "&" + preselected);'
               . 'else parent.right.location.replace(addr.replace(/(&|\?)preselected\[\d+\]=1/g, "") + "?" + preselected);'
               . '}}';
   }


   $script .= "\n// -->\n</script>\n";


   if (check_sm_version(1, 5, 2))
   {
      // TODO: 1.5.x output script differently...
   }
   else
   {
      echo $script;
   }
   
}



/** 
  * Insert needed script on message list page
  *
  * @param boolean $hide_current_count When TRUE, the current mailbox
  *                                    and its unread message count will
  *                                    not be exposed in JavaScript variables
  *                                    to the folder list screen so it cannot
  *                                    force a reload of this screen (OPTIONAL;
  *                                    default FALSE)
  *
  */
function folder_synch_insert_message_list_script($hide_current_count=FALSE)
{

   global $javascript_on;
   if (!$javascript_on) return;

   global $data_dir, $username, $imapConnection, $mailbox,
          $default_folder_list_resynch, $allow_override_folder_list_resynch,
          $default_message_list_resynch, $allow_override_message_list_resynch;

   if (empty($mailbox)) return;
   folder_synch_init();


   $message_list_resynch = $default_message_list_resynch;
   if ($allow_override_message_list_resynch)
      $message_list_resynch = getPref($data_dir, $username,
                                      'folder_synch_opt_right',
                                      $default_message_list_resynch);


   $folder_list_resynch = $default_folder_list_resynch;
   if ($allow_override_folder_list_resynch)
      $folder_list_resynch = getPref($data_dir, $username,
                                     'folder_synch_opt_left',
                                     $default_folder_list_resynch);


   if ((!$message_list_resynch || $hide_current_count) && !$folder_list_resynch)
      return;


   $box_status = sqimap_status_messages($imapConnection, $mailbox);
   $message_count = (empty($box_status['MESSAGES']) ? 0 : $box_status['MESSAGES']);
   $unseen_count = (empty($box_status['UNSEEN']) ? 0 : $box_status['UNSEEN']);
   $script = "<script type=\"text/javascript\" language=\"JavaScript\">\n<!--\n";


   // expose current mailbox unseen message count?
   //
   if (!$hide_current_count)
   {
      $script .= 'var mailbox_name = "' . $mailbox . '";'
               . 'var total_message_count = ' . $message_count . ';'
               . 'var unread_message_count = ' . $unseen_count . ';';
   }


   // Force folder list resynch upon message list actions?
   //
   // Add script that compares current folder list status for this
   // mailbox with what we have here in the message list and initiates
   // refresh if they are different.
   //
   if ($folder_list_resynch)
   {
      $script .= 'if (parent'
               . ' && parent.left'
               . ' && typeof parent.left.mailbox_message_counts != "undefined"'
               . ' && typeof parent.left.mailbox_unread_counts != "undefined"'
               . ' && "' . $mailbox . '" in parent.left.mailbox_message_counts'
               . ' && "' . $mailbox . '" in parent.left.mailbox_unread_counts'
               . ' && (parent.left.mailbox_message_counts["' . $mailbox . '"] != ' . $message_count
               . ' || parent.left.mailbox_unread_counts["' . $mailbox . '"] != ' . $unseen_count . ')) {'
              . 'parent.left.location.reload(true);'
              . '}';
   }


   $script .= "\n// -->\n</script>\n";


   if (check_sm_version(1, 5, 2))
   {
      // TODO: 1.5.x output script differently...
   }
   else
   {
      echo $script;
   }
   
}



/** 
  * Insert needed script on message read page
  *
  */
function folder_synch_insert_message_read_script()
{
   folder_synch_insert_message_list_script(TRUE);
}



