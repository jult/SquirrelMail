<?php

/**
  * SquirrelMail Multiple Attachments Plugin
  *
  * Copyright (c) 2012-2012 Paul Lesniewski <paul@squirrelmail.org>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage multiple_attachments
  *
  */



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function multiple_attachments_check_configuration()
{

   // make sure base config is available
   //
   if (!multiple_attachments_init())
   {
      do_err('Multiple Attachments plugin is missing its main configuration file', FALSE);
      return TRUE;
   }

}



/**
  * Initialize this plugin (load config values)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function multiple_attachments_init()
{

   if (!@include_once (SM_PATH . 'config/config_multiple_attachments.php'))
      if (!@include_once (SM_PATH . 'plugins/multiple_attachments/config.php'))
         if (!@include_once (SM_PATH . 'plugins/multiple_attachments/config_default.php'))
            return FALSE;

   return TRUE;

/* ----------  This is how to do the same thing using the Compatibility plugin
   return load_config('multiple_attachments',
                      array('../../config/config_multiple_attachments.php',
                            'config.php',
                            'config_default.php'),
                      TRUE, TRUE);
----------- */

}



/**
  * Start output caching so we can alter it before sending to client
  *
  */
function ma_start_output_cache()
{

   if (check_sm_version(1, 5, 2)) return;

   global $data_dir, $username, $number_of_attachment_inputs,
          $allow_dynamic_input_addition, $javascript_on,
          $number_of_attachment_inputs_allow_override;

   multiple_attachments_init();

   if ($number_of_attachment_inputs_allow_override)
      $number_of_attachment_inputs = getPref($data_dir, $username, 'number_of_attachment_inputs', $number_of_attachment_inputs);


   if (!(bool)ini_get('file_uploads')
    || ($number_of_attachment_inputs < 2
     && (!$javascript_on || !$allow_dynamic_input_addition)))
      return;

   ob_start();
}



/**
  * Add multiple attachment inputs to output for SquirrelMail versions 1.4.x
  *
  */
function ma_add_attachment_inputs_1_4()
{

   global $data_dir, $username, $number_of_attachment_inputs,
          $allow_dynamic_input_addition, $javascript_on,
          $number_of_attachment_inputs_allow_override;

   multiple_attachments_init();

   if ($number_of_attachment_inputs_allow_override)
      $number_of_attachment_inputs = getPref($data_dir, $username, 'number_of_attachment_inputs', $number_of_attachment_inputs);


   if (!(bool)ini_get('file_uploads')
    || ($number_of_attachment_inputs < 2
     && (!$javascript_on || !$allow_dynamic_input_addition)))
      return;


   $output .= ob_get_contents();
   ob_end_clean();


   $attachment_input = $dynamic_link = $script = '';


   // add dynamic link
   //
   if ($javascript_on && $allow_dynamic_input_addition)
   {
      // "more" is actually in the SquirrelMail domain, but
      // due to its context (for viewing more message recipients),
      // its translation in some languages doesn't work here
      //
      sq_change_text_domain('multiple_attachments');
      $more = _("more");
      sq_change_text_domain('squirrelmail');

      $dynamic_link = '&nbsp; <small><a href="javascript:void(0)" onclick="var row = document.getElementById(\'attachment_table\').insertRow(1); var cell = row.insertCell(0); cell.align = \'right\'; cell.valign = \'middle\'; cell.innerHTML = \'' . _("Attach:") . '\'; cell = row.insertCell(1); cell.align = \'left\'; cell.valign = \'middle\'; cell.innerHTML = get_new_attachment_input();">' . $more . "</a></small>\n";



      // small global JavaScript to initialize counter variable
      // and provide easy function for getting new attachment input tags
      //
      $script = "\n<script type=\"text/javascript\" language=\"JavaScript\">\n<!--\n"
              . "var attachment_counter = " . ($number_of_attachment_inputs - 1) . ";\n"
              . "function get_new_attachment_input()\n"
              . "{\n"
              . '   return \'<input name="attachfile_\' + (++attachment_counter) + \'" size="48" type="file" />\';'
              . "\n}"
              . "\n// -->\n</script>\n";
   }


   // add default inputs
   //
   for ($i = 0; $i < $number_of_attachment_inputs - 1; $i++)
      // note that we are intentionally in SquirrelMail text domain
      $attachment_input .= "<tr>\n"
         . html_tag('td', '', 'right', '', 'valign="middle"')
         . _("Attach:") . "</td>\n"
         . html_tag('td', '', 'left', '', 'valign="middle"')
         . '<input name="attachfile_' . ($i + 1) . '" size="48" type="file" />'
         . "\n</td>\n</tr>\n";


   // older SquirrelMail versions - need to insert it into cached output...
   //
   // adds name to table containing the inputs, then adds
   // the "dynamic" link after the first input and any
   // more default inputs in subsequent table rows if so configured
   //
   echo preg_replace('|(.*)<table (.*?<input name="attachfile" size="48" type="file".*?)</td>\s*</tr>|s',
                     '$1' . $script . '<table id="attachment_table" $2' . $dynamic_link . "</td>\n</tr>" . $attachment_input,
                     $output);

}



/**
  * Add multiple attachment inputs to output for SquirrelMail versions 1.5.x
  *
  */
function ma_add_attachment_inputs_1_5()
{

   global $data_dir, $username, $number_of_attachment_inputs,
          $allow_dynamic_input_addition, $javascript_on,
          $number_of_attachment_inputs_allow_override;

   multiple_attachments_init();

   if ($number_of_attachment_inputs_allow_override)
      $number_of_attachment_inputs = getPref($data_dir, $username, 'number_of_attachment_inputs', $number_of_attachment_inputs);


   if (!(bool)ini_get('file_uploads')
    || ($number_of_attachment_inputs < 2
     && (!$javascript_on || !$allow_dynamic_input_addition)))
      return;


   $attachment_input = $dynamic_link = $script = '';


   // add dynamic link
   //
   if ($javascript_on && $allow_dynamic_input_addition)
   {
      // "more" is actually in the SquirrelMail domain, but
      // due to its context (for viewing more message recipients),
      // its translation in some languages doesn't work here
      //
      sq_change_text_domain('multiple_attachments');
      $more = _("more");
      sq_change_text_domain('squirrelmail');

      $dynamic_link = '&nbsp; <small><a href="javascript:void(0)" onclick="var row = document.getElementById(\'attachment_table\').insertRow(1); row.className = \'header\'; var cell = row.insertCell(0); cell.className = \'fieldName\'; cell.width=\'1%\'; cell.style.whiteSpace=\'nowrap\'; cell.innerHTML = \'' . _("New attachment") . ':\'; cell = row.insertCell(1); cell.className = \'fieldValue\'; cell.innerHTML = get_new_attachment_input();">' . $more . "</a></small>\n";


      // small global JavaScript to initialize counter variable
      // and provide easy function for getting new attachment input tags
      //
      $script = "\n<script type=\"text/javascript\" language=\"JavaScript\">\n<!--\n"
              . "var attachment_counter = " . ($number_of_attachment_inputs - 1) . ";\n"
              . "function get_new_attachment_input()\n"
              . "{\n"
              . '   return \'<input name="attachfile_\' + (++attachment_counter) + \'" size="48" type="file" />\';'
              . "\n}"
              . "\n// -->\n</script>\n";
   }


   // add default inputs
   //
   for ($i = 0; $i < $number_of_attachment_inputs - 1; $i++)
      // note that we are intentionally in SquirrelMail text domain
      $attachment_input .= '<tr class="header">'
         . '<td class="fieldName" style="width: 1%; white-space: nowrap;">'
         . _("New attachment")
         . ':</td><td class="fieldValue">'
         . '<input type="file" name="attachfile_' . ($i + 1) . '" size="48" />'
         . '</td></tr>';


   return array('add_attachment_notes' => $script . $dynamic_link,
                'attachment_inputs'    => $attachment_input);

}



/**
  * Make sure multiple attachments aren't skipped if first upload input is blank
  *
  */
function ma_multiple_uploads_fix()
{

   // only need to do this one time (1.4.x loading_constants
   // hook is only executed once, but for 1.5.x, we use
   // prefs_backend, which is run a number of times)
   //
   static $execute_once = FALSE;
   if ($execute_once) return;
   $execute_once = TRUE;


   // iterate through all the uploaded file inputs
   // looking for compose attachment file uploads
   // and remove any that don't have an associated
   // upload temporary filename
   //
   global $_FILES;
   $new_upload_file_info_array = array();
   $i = 0;
   foreach ($_FILES as $key => $upload_file_info)
   {

      // for non-compose attachment uploads, just preserve them
      //
      if (strpos($key, 'attachfile') !== 0)
      {
         $new_upload_file_info_array[$key] = $upload_file_info;
         continue;
      }


      // now, handle compose attachment uploads
      //
      if (!empty($upload_file_info['tmp_name']))
      {
         if ($i === 0)
            $new_upload_file_info_array['attachfile'] = $upload_file_info;
         else
            $new_upload_file_info_array['attachfile_' . $i] = $upload_file_info;
         $i++;
      }

   }

   $_FILES = $new_upload_file_info_array;

}



/**
  * Handle multiple attachment file uploads
  *
  * @return boolean TRUE only if an error occurred trying
  *                 to process an attachment file upload;
  *                 FALSE under normal operation
  *
  */
function ma_handle_multiple_uploads($session)
{

   global $_FILES, $attachment_dir, $username, $composeMessage;

   $i = 1;
   while (isset($_FILES['attachfile_' . $i])
       && $_FILES['attachfile_' . $i]['tmp_name']
       && $_FILES['attachfile_' . $i]['tmp_name'] != 'none')
   {

      // this is just a duplication of the saveAttachedFiles()
      // function from src/compose.php, copied and slightly
      // modified from version 1.4.23 SVN on 2012-03-03

      /* get out of here if no file was attached at all */
      if (! is_uploaded_file($_FILES['attachfile_' . $i]['tmp_name']) ) {
         return true;
      }

      $hashed_attachment_dir = getHashedDir($username, $attachment_dir);
      $localfilename = GenerateRandomString(32, '', 7);
      $full_localfilename = "$hashed_attachment_dir/$localfilename";
      while (file_exists($full_localfilename)) {
         $localfilename = GenerateRandomString(32, '', 7);
         $full_localfilename = "$hashed_attachment_dir/$localfilename";
      }

      // FIXME: we SHOULD prefer move_uploaded_file over rename because
      // m_u_f works better with restricted PHP installs (safe_mode, open_basedir)
      if (!@rename($_FILES['attachfile_' . $i]['tmp_name'], $full_localfilename)) {
         if (!@move_uploaded_file($_FILES['attachfile_' . $i]['tmp_name'],$full_localfilename)) {
            return true;
         }
      }
      $type = strtolower($_FILES['attachfile_' . $i]['type']);
      $name = $_FILES['attachfile_' . $i]['name'];
      $composeMessage->initAttachment($type, $name, $localfilename);

      $i++;

   }

   return FALSE;

}



/**
  * Integrate options into SM options page
  *
  */
function ma_show_options($args)
{

   global $data_dir, $username, $number_of_attachment_inputs,
          $number_of_attachment_inputs_allow_override, $optpage_data;

   multiple_attachments_init();

   // get_current_hook_name() means we require the
   // Compatibility plugin, version 2.0.5+
   //
   $hook_name = get_current_hook_name($args);

   // 1.4.x - 1.5.0:  options go on display options page
   // 1.5.1 and up:  options go on compose options page
   //
   if (check_sm_version(1, 5, 1) && $hook_name != 'optpage_loadhook_compose')
      return;
   if (!check_sm_version(1, 5, 1) && $hook_name != 'optpage_loadhook_display')
      return;


   // placement in option groups differs per SM version
   //
   if ($hook_name == 'optpage_loadhook_display')
      $option_index = 2;
   else
      $option_index = 0;


   sq_change_text_domain('multiple_attachments');


   if ($number_of_attachment_inputs_allow_override)
   {
      $number_of_attachment_inputs = getPref($data_dir, $username, 'number_of_attachment_inputs', $number_of_attachment_inputs);
      $optpage_data['vals'][$option_index][] = array(
         'name'          => 'number_of_attachment_inputs',
         'caption'       => _("Number Of Attachment Upload Inputs"),
         'type'          => SMOPT_TYPE_STRLIST,
         'initial_value' => $number_of_attachment_inputs,
         'refresh'       => SMOPT_REFRESH_NONE,
         'posvals'       => array(1 => 1,
                                  2 => 2,
                                  3 => 3,
                                  4 => 4,
                                  5 => 5,
                                  6 => 6,
                                  7 => 7,
                                  8 => 8,
                                  9 => 9,
                                  10 => 10),
      );
   }


   sq_change_text_domain('squirrelmail');

}



