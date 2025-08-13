<?php

/**
  * SquirrelMail Mark Read Plugin
  * Copyright (c) 2004-2005 Dave Kliczbor <maligree@gmx.de>
  * Copyright (c) 2003-2009 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2004 Ferdie Ferdsen <ferdie.ferdsen@despam.de>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage mark_read
  *
  */



/**
  * Places text and widgets on the folder page
  *
  */
function mark_read_show_options_do() 
{

   global $username, $data_dir, $color, $javascript_on,
          $show_read_button_allow_override, $confirm_read_button,
          $folders_to_show_read_button, $folders_to_not_show_read_button,
          $show_unread_button_allow_override, $confirm_unread_button,
          $folders_to_show_unread_button, $folders_to_not_show_unread_button,
          $show_read_link_allow_override, $confirm_read_link,
          $folders_to_show_read_link, $folders_to_not_show_read_link,
          $show_unread_link_allow_override, $confirm_unread_link,
          $folders_to_show_unread_link, $folders_to_not_show_unread_link;



   // no user config?  bail.
   //
   include_once(SM_PATH . 'plugins/mark_read/functions.php');
   mark_read_init();
   if (!$show_read_button_allow_override
    && !$show_unread_button_allow_override
    && !$show_read_link_allow_override
    && !$show_unread_link_allow_override)
      return;


   // grab new settings if user had just pressed the Save button
   //
   if (sqgetGlobalVar('mark_read_form', $mark_read_form, SQ_FORM)
    && $mark_read_form)
   {

      // confirm read link is easy...
      //
      if ($show_read_link_allow_override)
      {
         if (sqgetGlobalVar('confirm_read_link', $confirm_read_link, SQ_FORM)
          && !empty($confirm_read_link))
            setPref($data_dir, $username, 'confirm_read_link', 1);
         else
            setPref($data_dir, $username, 'confirm_read_link', 0);
      }


      // so is comfirm unread link
      //
      if ($show_unread_link_allow_override)
      {
         if (sqgetGlobalVar('confirm_unread_link', $confirm_unread_link, SQ_FORM)
          && !empty($confirm_unread_link))
            setPref($data_dir, $username, 'confirm_unread_link', 1);
         else
            setPref($data_dir, $username, 'confirm_unread_link', 0);
      }


      // as is comfirm read button
      //
      if ($show_read_button_allow_override)
      {
         if (sqgetGlobalVar('confirm_read_button', $confirm_read_button, SQ_FORM)
          && !empty($confirm_read_button))
            setPref($data_dir, $username, 'confirm_read_button', 1);
         else
            setPref($data_dir, $username, 'confirm_read_button', 0);
      }


      // and comfirm unread button
      //
      if ($show_unread_button_allow_override)
      {
         if (sqgetGlobalVar('confirm_unread_button', $confirm_unread_button, SQ_FORM)
          && !empty($confirm_unread_button))
            setPref($data_dir, $username, 'confirm_unread_button', 1);
         else
            setPref($data_dir, $username, 'confirm_unread_button', 0);
      }


      // get folders that should have a read button
      //
      if ($show_read_button_allow_override)
      {
         $mark_read_show_read_button = 0;
         sqgetGlobalVar('mark_read_show_read_button', $mark_read_show_read_button, SQ_FORM);
         if (!empty($mark_read_show_read_button) && is_array($mark_read_show_read_button))
         {
            $allFolders = '';
            foreach ($mark_read_show_read_button as $folder)
               $allFolders .= $folder . '###';
         }
         else
            // cannot use an empty value here, otherwise the plugin defaults
            // will take over again when user intended for NO folders to be
            // the correct behavior, so we use the set string "NONE"
            //
            $allFolders = 'NONE';

         setPref($data_dir, $username, 'mark_read_show_read_button', $allFolders);
      }


      // get folders that should have a read link
      //
      if ($show_read_link_allow_override)
      {
         $mark_read_show_read_link = 0;
         sqgetGlobalVar('mark_read_show_read_link', $mark_read_show_read_link, SQ_FORM);
         if (!empty($mark_read_show_read_link) && is_array($mark_read_show_read_link))
         {
            $allFolders = '';
            foreach ($mark_read_show_read_link as $folder)
               $allFolders .= $folder . '###';
         }
         else
            // cannot use an empty value here, otherwise the plugin defaults
            // will take over again when user intended for NO folders to be
            // the correct behavior, so we use the set string "NONE"
            //
            $allFolders = 'NONE';

         setPref($data_dir, $username, 'mark_read_show_read_link', $allFolders);
      }


      // get folders that should have a unread button
      //
      if ($show_unread_button_allow_override)
      {
         $mark_read_show_unread_button = 0;
         sqgetGlobalVar('mark_read_show_unread_button', $mark_read_show_unread_button, SQ_FORM);
         if (!empty($mark_read_show_unread_button) && is_array($mark_read_show_unread_button))
         {
            $allFolders = '';
            foreach ($mark_read_show_unread_button as $folder)
               $allFolders .= $folder . '###';
         }
         else
            // cannot use an empty value here, otherwise the plugin defaults
            // will take over again when user intended for NO folders to be
            // the correct behavior, so we use the set string "NONE"
            //
            $allFolders = 'NONE';

         setPref($data_dir, $username, 'mark_read_show_unread_button', $allFolders);
      }


      // get folders that should have a unread link
      //
      if ($show_unread_link_allow_override)
      {
         $mark_read_show_unread_link = 0;
         sqgetGlobalVar('mark_read_show_unread_link', $mark_read_show_unread_link, SQ_FORM);
         if (!empty($mark_read_show_unread_link) && is_array($mark_read_show_unread_link))
         {
            $allFolders = '';
            foreach ($mark_read_show_unread_link as $folder)
               $allFolders .= $folder . '###';
         }
         else
            // cannot use an empty value here, otherwise the plugin defaults
            // will take over again when user intended for NO folders to be
            // the correct behavior, so we use the set string "NONE"
            //
            $allFolders = 'NONE';

         setPref($data_dir, $username, 'mark_read_show_unread_link', $allFolders);
      }

   }



   // grab user preference for confirm read link
   //
   if ($show_read_link_allow_override)
      $confirm_read_link = getPref($data_dir, $username,
                                   'confirm_read_link',
                                   $confirm_read_link);



   // grab user preference for confirm read button
   //
   if ($show_read_button_allow_override)
      $confirm_read_button = getPref($data_dir, $username,
                                     'confirm_read_button',
                                     $confirm_read_button);



   // grab user preference for confirm unread link
   //
   if ($show_unread_link_allow_override)
      $confirm_unread_link = getPref($data_dir, $username,
                                     'confirm_unread_link',
                                     $confirm_unread_link);



   // grab user preference for confirm unread button
   //
   if ($show_unread_button_allow_override)
      $confirm_unread_button = getPref($data_dir, $username,
                                       'confirm_unread_button',
                                       $confirm_unread_button);



   // grab user preference for read button folders
   //
   if ($show_read_button_allow_override)
      $allFolders = getPref($data_dir, $username,
                            'mark_read_show_read_button',
                            NULL);
   else
      $allFolders = NULL;



   // reformat folder list if not NULL (which means defaults apply below)
   //
   if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
   if (!is_null($allFolders)) $mark_read_show_read_button = explode('###', $allFolders);
   else $mark_read_show_read_button = NULL;



   // grab user preference for read link folders
   //
   if ($show_read_link_allow_override)
      $allFolders = getPref($data_dir, $username,
                            'mark_read_show_read_link',
                            NULL);
   else
      $allFolders = NULL;



   // reformat folder list if not NULL (which means defaults apply below)
   //
   if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
   if (!is_null($allFolders)) $mark_read_show_read_link = explode('###', $allFolders);
   else $mark_read_show_read_link = NULL;



   // grab user preference for unread button folders
   //
   if ($show_unread_button_allow_override)
      $allFolders = getPref($data_dir, $username,
                            'mark_read_show_unread_button',
                            NULL);
   else
      $allFolders = NULL;



   // reformat folder list if not NULL (which means defaults apply below)
   //
   if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
   if (!is_null($allFolders)) $mark_read_show_unread_button = explode('###', $allFolders);
   else $mark_read_show_unread_button = NULL;



   // grab user preference for unread link folders
   //
   if ($show_unread_link_allow_override)
      $allFolders = getPref($data_dir, $username,
                            'mark_read_show_unread_link',
                            NULL);
   else
      $allFolders = NULL;



   // reformat folder list if not NULL (which means defaults apply below)
   //
   if ($allFolders == 'NONE') $allFolders = ''; // see note elsewhere about why we use "NONE"
   if (!is_null($allFolders)) $mark_read_show_unread_link = explode('###', $allFolders);
   else $mark_read_show_unread_link = NULL;



   // prepare folder list for easy use by template
   //
   // create an array where each entry is a seven-element array keyed
   // by "displayable", "option_value", "show_unread_link", "show_read_link",
   // "show_read_button", "show_unread_button" and "is_special",
   // where the last five are boolean values
   //
   $mr_folder_listing = array();
/* ----- already tested these at the top of this function...
   if ($show_read_button_allow_override
    || $show_unread_button_allow_override
    || $show_read_link_allow_override
    || $show_unread_link_allow_override)
----- */
   {
      global $mr_folder_listing, $trash_folder, $boxes, $nbsp;
      if (!check_sm_version(1, 5, 2)) $nbsp = '&nbsp;';
      for ($i = 0; $i < count($boxes); $i++) 
      {
/* ----- nah, we now allow the trash folder - why not?
         if (strtolower($boxes[$i]['unformatted']) != strtolower($trash_folder)
          && strtolower($boxes[$i]['unformatted-dm'] != 'inbox.trash'))
----- */
         {

            $mr_folder_listing[$i]['option_value'] = $boxes[$i]['unformatted-dm'];
            $mr_folder_listing[$i]['displayable'] = str_replace(' ', $nbsp, imap_utf7_decode_local($boxes[$i]['unformatted-disp']));


            if (isSpecialMailbox($mr_folder_listing[$i]['option_value']))
               $mr_folder_listing[$i]['is_special'] = 1;
            else
               $mr_folder_listing[$i]['is_special'] = 0;



            // if the user specified a list before, just use it to
            // test for what folders have the read link turned on
            //
            if (is_array($mark_read_show_read_link))
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $mark_read_show_read_link))
                  $mr_folder_listing[$i]['show_read_link'] = 1;
               else
                  $mr_folder_listing[$i]['show_read_link'] = 0;
            }


            // or apply the defaults from the configuration file
            //
            // (link enabled if in default list, or if NOT in the
            // default "don't show" list (and that list is non-empty))
            //
            else
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $folders_to_show_read_link)
                || (!empty($folders_to_not_show_read_link) && !in_array($mr_folder_listing[$i]['option_value'], $folders_to_not_show_read_link)))
                  $mr_folder_listing[$i]['show_read_link'] = 1;
               else
                  $mr_folder_listing[$i]['show_read_link'] = 0;
            }



            // if the user specified a list before, just use it to
            // test for what folders have the read button turned on
            //
            if (is_array($mark_read_show_read_button))
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $mark_read_show_read_button))
                  $mr_folder_listing[$i]['show_read_button'] = 1;
               else
                  $mr_folder_listing[$i]['show_read_button'] = 0;
            }


            // or apply the defaults from the configuration file
            //
            // (button enabled if in default list, or if NOT in the
            // default "don't show" list (and that list is non-empty))
            //
            else
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $folders_to_show_read_button)
                || (!empty($folders_to_not_show_read_button) && !in_array($mr_folder_listing[$i]['option_value'], $folders_to_not_show_read_button)))
                  $mr_folder_listing[$i]['show_read_button'] = 1;
               else
                  $mr_folder_listing[$i]['show_read_button'] = 0;
            }



            // if the user specified a list before, just use it to
            // test for what folders have the unread link turned on
            //
            if (is_array($mark_read_show_unread_link))
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $mark_read_show_unread_link))
                  $mr_folder_listing[$i]['show_unread_link'] = 1;
               else
                  $mr_folder_listing[$i]['show_unread_link'] = 0;
            }


            // or apply the defaults from the configuration file
            //
            // (link enabled if in default list, or if NOT in the
            // default "don't show" list (and that list is non-empty))
            //
            else
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $folders_to_show_unread_link)
                || (!empty($folders_to_not_show_unread_link) && !in_array($mr_folder_listing[$i]['option_value'], $folders_to_not_show_unread_link)))
                  $mr_folder_listing[$i]['show_unread_link'] = 1;
               else
                  $mr_folder_listing[$i]['show_unread_link'] = 0;
            }



            // if the user specified a list before, just use it to
            // test for what folders have the unread button turned on
            //
            if (is_array($mark_read_show_unread_button))
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $mark_read_show_unread_button))
                  $mr_folder_listing[$i]['show_unread_button'] = 1;
               else
                  $mr_folder_listing[$i]['show_unread_button'] = 0;
            }


            // or apply the defaults from the configuration file
            //
            // (button enabled if in default list, or if NOT in the
            // default "don't show" list (and that list is non-empty))
            //
            else
            {
               if (in_array($mr_folder_listing[$i]['option_value'], $folders_to_show_unread_button)
                || (!empty($folders_to_not_show_unread_button) && !in_array($mr_folder_listing[$i]['option_value'], $folders_to_not_show_unread_button)))
                  $mr_folder_listing[$i]['show_unread_button'] = 1;
               else
                  $mr_folder_listing[$i]['show_unread_button'] = 0;
            }

         }
      }
   }


   sq_change_text_domain('mark_read');


   if (check_sm_version(1, 5, 2))
   {
      global $oTemplate;

      $oTemplate->assign('show_read_button_allow_override', $show_read_button_allow_override);
      $oTemplate->assign('show_read_link_allow_override', $show_read_link_allow_override);
      $oTemplate->assign('show_unread_button_allow_override', $show_unread_button_allow_override);
      $oTemplate->assign('show_unread_link_allow_override', $show_unread_link_allow_override);
      $oTemplate->assign('confirm_read_button', $confirm_read_button);
      $oTemplate->assign('confirm_read_link', $confirm_read_link);
      $oTemplate->assign('confirm_unread_button', $confirm_unread_button);
      $oTemplate->assign('confirm_unread_link', $confirm_unread_link);
      $oTemplate->assign('mr_folder_listing', $mr_folder_listing);
      $oTemplate->assign('javascript_on', $javascript_on);
      $oTemplate->assign('color', $color);

      $output = $oTemplate->fetch('plugins/mark_read/mark_read_options.tpl');
      return array('folders_bottom' => $output);
   }
   else
   {
      global $t;
      $t = array(); // no need to put config vars herein, they are already globalized

      include_once(SM_PATH . 'plugins/mark_read/templates/default/mark_read_options.tpl');
   }


   sq_change_text_domain('squirrelmail');

}



