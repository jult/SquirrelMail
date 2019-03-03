<?php

   global $startup_location, $startup_folder,
          $startup_plugin, $startup_allow_user_config, $startup_plugin_list;


   // Should users be able to modify their start location?
   //
   // 0 = no
   // 1 = yes
   //
   $startup_allow_user_config = 1;


   // This setting determines the default startup behavior; does it 
   // go to a folder or to a plugin (or is it turned off)?
   //
   // 0 = Off
   // 1 = Folder
   // 2 = Plugin (Called "Other" in the options page interface)
   //
   $startup_location = 0;


   // This is the default startup folder for all users.  Set to empty
   // string to disable.  Note that the format of this value depends
   // on what IMAP server you have.  If you are not familiar with the
   // folder naming conventions used in your IMAP server, the easiest
   // thing to do is to enable $startup_allow_user_config, make your
   // own startup folder setting, and then look in your personal pref
   // file:
   //
   // /path/to/squirrelmail/data/directory/username.pref
   //
   // for a line that looks like this:
   //
   // startup_folder_folder=INBOX.Business
   //
   // That is the value you'd use here if you want a default folder
   // for all of your users.
   //
   $startup_folder = '';
   //$startup_folder = 'INBOX.Business';


   // This is where you build the list of plugins that should
   // be available as startup locations for your users.  This
   // is a list of key/value pairs, where the key is the actual
   // URL of the plugin's options page relative to the plugins 
   // directory.  The value is what is displayed to the user 
   // to identify the plugin location.
   //
   // To determine what the URL of a plugin, you can usually
   // just browse to the Options page and look at the links there.
   //
   // For example, with the weather plugin installed, if you navigate 
   // to the Options page and hover your cursor over the Weather link,
   // the status bar should show a link similar to:
   //    
   //    http://yourdomain.com/webmail/plugins/weather/options.php
   // 
   // In this case, the Weather plugin's starup URL would be
   // 'weather/options.php'.
   //
   // Below are several working examples for actual SquirrelMail
   // plugins.  Of course, you'd need to have them installed (and
   // activated) to use them as startup locations, and if you do
   // so, remember to remove the comment markers at the beginning
   // of the relevant lines.
   //
   $startup_plugin_list = array(
//      'file_manager/file_manager.php'            => 'File Manager',
//      'filters/options.php'                      => 'Message Filters',
//      'filters/spamoptions.php'                  => 'SPAM Filters',
//      'mail_fetch/options.php'                   => 'POP3 Fetch Mail',
//      'translate/options.php'                    => 'Translation Options',
//      'change_mysqlpass/options.php'             => 'Change Password',
//      'local_autorespond_forward/options.php'    => 'Autoresponder / Mail Forwarding Options',
//      'courier_vacation/options.php'             => 'Vacation/Autoresponder',
//      'calendar/admin_options.php'               => 'Shared Calendar Administration',
//      'weather/options.php'                      => 'Weather',
//      'unsafe_image_rules/options.php'           => 'Unsafe Image Rules',
//      'archive_mail/includes/display_inside.php' => 'Archive Settings',
//      'newmail/newmail_opt.php'                  => 'NewMail Options',
//      'squirrelspell/sqspell_options.php'        => 'SpellChecker Options',
   );


   // The is the default startup plugin location, and it should 
   // be in the same format as one of the keys to the list above.
   //
   $startup_plugin = '';
   //$startup_plugin = 'weather/options.php';


