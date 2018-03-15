<?php

/**
  * SquirrelMail Autocomplete Plugin
  *
  * Copyright (c) 2003-2012 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2005 Graham <gsm-smpi@soundclashchampions.com>
  * Copyright (c) 2001 Tyler Akins
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage autocomplete
  *
  */


/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function autocomplete_check_configuration()
{

   // make sure base config is available
   //
   if (!autocomplete_init())
   {
      do_err('Autocomplete plugin is missing its main configuration file', FALSE);
      return TRUE;
   }

}



/**
  * Insert JavaScript for Autocomplete functionality
  *
  */
function ac_compose_bottom()
{

   global $javascript_on;


   if (!$javascript_on)
      return;


   global $data_dir, $username, $addrsrch_fullname, $autocomplete_enable,
          $autocomplete_enable_allow_override, $enable_remote_address_book_preload,
          $autocomplete_preload, $autocomplete_preload_allow_override,
          $autocomplete_restrict_matching, $autocomplete_restrict_matching_allow_override,
          $autocomplete_match_nicknames, $autocomplete_match_nicknames_allow_override,
          $autocomplete_match_fullnames, $autocomplete_match_fullnames_allow_override,
          $autocomplete_match_emails, $autocomplete_match_emails_allow_override,
          $autocomplete_by_tab, $autocomplete_by_tab_allow_override,
          $autocomplete_match_case, $autocomplete_match_case_allow_override,
          $autocomplete_only_personal, $autocomplete_only_personal_allow_override,
          $autocomplete_minimum_number_characters, $max_list_height, $ac_debug,
          $autocomplete_minimum_number_characters_allow_override;
   autocomplete_init();


   $addrsrch_fullname = getPref($data_dir, $username, 'addrsrch_fullname', 'fullname');


   if ($autocomplete_enable_allow_override)
   {
      // note legacy user pref name has "d" on the end
      $autocomplete_enable = getPref($data_dir, $username,
                                     'autocomplete_enabled',
                                     $autocomplete_enable);
   }


   if (!$autocomplete_enable) return;


   if ($autocomplete_match_nicknames_allow_override)
   {
      $autocomplete_match_nicknames = getPref($data_dir, $username,
                                      'autocomplete_match_nicknames',
                                      $autocomplete_match_nicknames);
   }


   if ($autocomplete_match_fullnames_allow_override)
   {
      $autocomplete_match_fullnames = getPref($data_dir, $username,
                                      'autocomplete_match_fullnames',
                                      $autocomplete_match_fullnames);
   }


   if ($autocomplete_match_emails_allow_override)
   {
      $autocomplete_match_emails = getPref($data_dir, $username,
                                   'autocomplete_match_emails',
                                   $autocomplete_match_emails);
   }


   // if we don't have any fields to match on then no use continuing
   //
   if (!$autocomplete_match_nicknames &&!$autocomplete_match_fullnames
    && !$autocomplete_match_emails)
      return;


   if ($autocomplete_only_personal_allow_override)
   {
      $autocomplete_only_personal = getPref($data_dir, $username,
                                    'autocomplete_only_personal',
                                    $autocomplete_only_personal);
   }


   if ($autocomplete_preload_allow_override)
   {
      $autocomplete_preload = getPref($data_dir, $username,
                                      'autocomplete_preload',
                                      $autocomplete_preload);
   }


   if ($autocomplete_restrict_matching_allow_override)
   {
      $autocomplete_restrict_matching = getPref($data_dir, $username,
                                        'autocomplete_restrict_matching',
                                        $autocomplete_restrict_matching);
   }


   if ($autocomplete_match_case_allow_override)
   {
      $autocomplete_match_case = getPref($data_dir, $username,
                                         'autocomplete_match_case',
                                         $autocomplete_match_case);
   }


   if ($autocomplete_minimum_number_characters_allow_override)
   {
      $autocomplete_minimum_number_characters = getPref($data_dir, $username,
                                                'autocomplete_minimum_number_characters',
                                                $autocomplete_minimum_number_characters);
   }


   if ($autocomplete_by_tab_allow_override)
   {
      $autocomplete_by_tab = getPref($data_dir, $username,
                                     'autocomplete_by_tab',
                                     $autocomplete_by_tab);
   }


   // get all user contacts if preload is enabled
   //
   if (!$autocomplete_preload)
      $addresses = array();
   else
   {

      include_once (SM_PATH . 'functions/addressbook.php');


      // initialize addressbook and get addresses
      //
      $abook = addressbook_init(FALSE, !$enable_remote_address_book_preload);
      if ($autocomplete_only_personal)
      {
         $backend = $abook->localbackend;

         // hmmm, no personal address book?  this is a problem,
         // but we'll let the admin figure it out elsewhere
         //
         if ($backend == 0)
            $backend = -1;
      }
      else
         $backend = -1;
      $addresses = $abook->list_addr($backend);

      // some sort of error or no entries
      //
      if (!is_array($addresses) || !count($addresses))
         return;

   }


   // variables used in templates
   //
   global $color, $javascript_contact_array, $squirrelmail_language;
   $output = '';


   // convert address list into JavaScript-compatible array
   // 
   // resulting array should look like (nickname, full name, email):
   // 
   // [["Andy","Andrew Smith","andy@example.com"],["JR","John Jones","jrj@example.com"]]
   // 
   $javascript_contact_array = '';
   foreach ($addresses as $contact)
      $javascript_contact_array .= '["'
                                 . ac_encode_javascript_string($contact['nickname']) . '","'
                                 . ac_encode_javascript_string(
                                      ($squirrelmail_language == 'ja_JP'
                                         ? $contact['lastname'] . ' ' . $contact['firstname']
                                         : $contact['name']))
                                 . '","'
                                 . ac_encode_javascript_string($contact['email']) . '"],';
   $javascript_contact_array = '[' . trim($javascript_contact_array, ',') . ']';


   // output stylesheet
   //
   if (check_sm_version(1, 5, 2))
   {
      global $t;
      $t = array(); // no need to put config vars herein, they are already globalized
      ob_start();
      include_once(SM_PATH . 'plugins/autocomplete/templates/default/autocomplete_stylesheet.tpl');
      $output .= ob_get_contents();
      ob_end_clean();
   }
   else
   {
      global $t;
      $t = array(); // no need to put config vars herein, they are already globalized
      include_once(SM_PATH . 'plugins/autocomplete/templates/default/autocomplete_stylesheet.tpl');
   }


   // output JavaScript - Autocomplete main code
   //
   if (check_sm_version(1, 5, 2))
   {
      global $t;
      $t = array(); // no need to put config vars herein, they are already globalized
      ob_start();
      include_once(SM_PATH . 'plugins/autocomplete/templates/default/autocomplete_javascript.tpl');
      $output .= ob_get_contents();
      ob_end_clean();
   }
   else
   {
      global $t;
      $t = array(); // no need to put config vars herein, they are already globalized
      include_once(SM_PATH . 'plugins/autocomplete/templates/default/autocomplete_javascript.tpl');
   }


   // for SquirrelMail versions 1.5.2+ we still need to hand back our output
   //
   if (check_sm_version(1, 5, 2))
      return array('compose_bottom' => $output);

}



/**
  * Integrate options into SM options page
  *
  */
function ac_show_options($args)
{
   
   global $data_dir, $username, $optpage_data, $autocomplete_enable,
          $autocomplete_enable_allow_override, $autocomplete_preload,
          $autocomplete_preload_allow_override, $autocomplete_restrict_matching,
          $autocomplete_restrict_matching_allow_override,
          $autocomplete_by_tab, $autocomplete_by_tab_allow_override,
          $autocomplete_match_nicknames, $autocomplete_match_nicknames_allow_override,
          $autocomplete_match_fullnames, $autocomplete_match_fullnames_allow_override,
          $autocomplete_match_emails, $autocomplete_match_emails_allow_override,
          $autocomplete_match_case, $autocomplete_match_case_allow_override,
          $autocomplete_only_personal, $autocomplete_only_personal_allow_override,
          $autocomplete_minimum_number_characters,
          $autocomplete_minimum_number_characters_allow_override;
   autocomplete_init();
   
   // necessitates Compatibility plugin
   //
   $hook_name = get_current_hook_name($args);
   
   // 1.4.x - 1.5.0:  options go on display options page
   // 1.5.1 and up:  options go on compose options page
   // 
   if (check_sm_version(1, 5, 1) && $hook_name != 'optpage_loadhook_compose')
      return;
   if (!check_sm_version(1, 5, 1) && $hook_name != 'optpage_loadhook_display')
      return;


   sq_change_text_domain('autocomplete');
   
   $my_optpage_values = array();


   if ($autocomplete_enable_allow_override)
   {
      // note legacy user pref name has "d" on the end
      $autocomplete_enable = getPref($data_dir, $username,
                                     'autocomplete_enabled',
                                     $autocomplete_enable);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_enabled',
         'caption'       => _("Search Contacts As You Type"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_enable,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_only_personal_allow_override)
   {
      $autocomplete_only_personal = getPref($data_dir, $username,
                                    'autocomplete_only_personal',
                                    $autocomplete_only_personal);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_only_personal',
         'caption'       => _("Only Search Personal Contacts"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_only_personal,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_preload_allow_override)
   {
      $autocomplete_preload = getPref($data_dir, $username,
                                      'autocomplete_preload',
                                      $autocomplete_preload);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_preload',
         'caption'       => _("Pre-load Contacts"),
         'trailing_text' => _("(Faster lookup but slower loading)"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_preload,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_match_case_allow_override)
   {
      $autocomplete_match_case = getPref($data_dir, $username,
                                         'autocomplete_match_case',
                                         $autocomplete_match_case);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_match_case',
         'caption'       => _("Match Case"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_match_case,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_restrict_matching_allow_override)
   {
      $autocomplete_restrict_matching = getPref($data_dir, $username,
                                        'autocomplete_restrict_matching',
                                        $autocomplete_restrict_matching);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_restrict_matching',
         'caption'       => _("Match Only Beginning Of Contact Fields"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_restrict_matching,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_match_nicknames_allow_override)
   {
      $autocomplete_match_nicknames = getPref($data_dir, $username,
                                      'autocomplete_match_nicknames',
                                      $autocomplete_match_nicknames);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_match_nicknames',
         'caption'       => _("Search Contact Nicknames"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_match_nicknames,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_match_fullnames_allow_override)
   {
      $autocomplete_match_fullnames = getPref($data_dir, $username,
                                      'autocomplete_match_fullnames',
                                      $autocomplete_match_fullnames);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_match_fullnames',
         'caption'       => _("Search Contact Full Names"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_match_fullnames,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_match_emails_allow_override)
   {
      $autocomplete_match_emails = getPref($data_dir, $username,
                                   'autocomplete_match_emails',
                                   $autocomplete_match_emails);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_match_emails',
         'caption'       => _("Search Contact Email Addresses"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_match_emails,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_minimum_number_characters_allow_override)
   {
      $autocomplete_minimum_number_characters = getPref($data_dir, $username,
                                                'autocomplete_minimum_number_characters',
                                                $autocomplete_minimum_number_characters);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_minimum_number_characters',
         'caption'       => _("Number Of Characters Typed To Trigger Search"),
         'type'          => SMOPT_TYPE_INTEGER,
         'size'          => SMOPT_SIZE_TINY,
         'initial_value' => $autocomplete_minimum_number_characters,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if ($autocomplete_by_tab_allow_override)
   {
      $autocomplete_by_tab = getPref($data_dir, $username,
                                     'autocomplete_by_tab',
                                     $autocomplete_by_tab);
      $my_optpage_values[] = array(
         'name'          => 'autocomplete_by_tab',
         'caption'       => _("Select Contact Upon Tab Press"),
         'type'          => SMOPT_TYPE_BOOLEAN,
         'initial_value' => $autocomplete_by_tab,
         'refresh'       => SMOPT_REFRESH_NONE,
      );
   }


   if (!empty($my_optpage_values))
   {
      $optpage_data['grps']['autocomplete'] = _("Address Autocompletion");
      $optpage_data['vals']['autocomplete'] = $my_optpage_values;
   }


   sq_change_text_domain('squirrelmail');

}



