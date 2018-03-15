<?php

/**
  * SquirrelMail Random Login Image Plugin
  *
  * Copyright (c) 2011-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002 Tracy McKibben <tracy@mckibben.d2g.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage login_image
  *
  */



/**
  * Validate that this plugin is configured correctly
  *
  * @return boolean Whether or not there was a
  *                 configuration error for this plugin.
  *
  */
function login_image_check_configuration()
{

   // make sure base config is available
   //
   if (!login_image_init())
   {
      do_err('Random Login Image plugin is missing its main configuration file', FALSE);
      return TRUE;
   }


   // only need to do this pre-1.5.2, as 1.5.2 will make this
   // check for us automatically
   //
   if (!check_sm_version(1, 5, 2))
   {

      // 1.4.12 up to but NOT including 1.5.0 are OK
      //
      if (check_sm_version(1, 4, 12) && !check_sm_version(1, 5, 0))
      {
         return FALSE;
      }


      // try to find Compatibility, and then that it is v2.0.10+
      //
      if (function_exists('check_plugin_version')
       && check_plugin_version('compatibility', 2, 0, 10, TRUE))
         return FALSE;


      // something went wrong
      //
      do_err('Random Login Image plugin requires the Compatibility plugin version 2.0.10+', FALSE);
      return TRUE;

   }


   return FALSE;

}



/**
  * Initialize this plugin (load config values)
  *
  * @param boolean $debug When TRUE, do not suppress errors when including
  *                       configuration files (OPTIONAL; default FALSE)
  *
  * @return boolean FALSE if no configuration file could be loaded, TRUE otherwise
  *
  */
function login_image_init($debug=FALSE)
{

   if ($debug)
   {
      if (!include_once(SM_PATH . 'config/config_login_image.php'))
         if (!include_once(SM_PATH . 'plugins/login_image/config.php'))
            if (!include_once(SM_PATH . 'plugins/login_image/config_default.php'))
               return FALSE;
   }
   else
   {
      if (!@include_once(SM_PATH . 'config/config_login_image.php'))
         if (!@include_once(SM_PATH . 'plugins/login_image/config.php'))
            if (!@include_once(SM_PATH . 'plugins/login_image/config_default.php'))
               return FALSE;
   }


   return TRUE;

}



/**
  * Show login image on login page
  *
  */
function show_login_image()
{

   global $local_image_directory, $local_image_ignore_list, $local_image_file_height,
          $login_image_directory_separator, $local_image_files_cache_seconds,
          $login_image_remote_image_sources, $login_image_override_org_logo,
          $login_image_verify_image_links;

   login_image_init();


   // first attempt to retrieve the local file source
   //
   if (!empty($local_image_directory))
   {
      $files = get_local_image_files($local_image_directory, $local_image_ignore_list,
                                     $local_image_files_cache_seconds,
                                     $login_image_directory_separator);

      // now add the sizing for local files
      //
      $file_sizes = array_pad(array(), sizeof($files), $local_image_file_height);
   }
   else
   {
      $files = array();
      $file_sizes = array();
   }


   // now, iterate through any remote sources
   //
   if (is_array($login_image_remote_image_sources) && !empty($login_image_remote_image_sources))
   {
      foreach ($login_image_remote_image_sources as $remote_source)
      {
         if ($image_info = get_remote_image($remote_source, $login_image_verify_image_links))
            list($files[], $file_sizes[]) = $image_info;
      }
   }


   // choose an image
   //
   if (!check_sm_version(1, 5, 2)) sq_mt_randomize();
   $the_chosen_one = mt_rand(0, sizeof($files) - 1);


   // ...and display it
   //
   if ($login_image_override_org_logo)
   {
      global $org_logo, $org_logo_height;
      $org_logo = $files[$the_chosen_one];
      $org_logo_height = $file_sizes[$the_chosen_one];

      if (check_sm_version(1, 5, 2))
      {
         global $logo_str, $org_name, $org_logo_width, $oTemplate;

         if (isset($org_logo_width) && is_numeric($org_logo_width) && $org_logo_width > 0)
            $width = $org_logo_width;
         else
            $width = '';

         if (isset($org_logo_height) && is_numeric($org_logo_height) && $org_logo_height > 0)
            $height = $org_logo_height;
         else
            $height = '';

         $logo_str = create_image($org_logo, sprintf(_("%s Logo"), $org_name),
                                  $width, $height, '', 'sqm_loginImage');

         $oTemplate->assign('logo_str', $logo_str, FALSE);
      }
   }
   else
   {
      if (check_sm_version(1, 5, 2))
      {
         global $oTemplate;
         $oTemplate->assign('random_login_image_src', $files[$the_chosen_one]);
         $oTemplate->assign('random_login_image_height', $file_sizes[$the_chosen_one]);
         $output = $oTemplate->fetch('plugins/login_image/login_image.tpl');
         return array('login_top' => $output);
      }
      else
      {
         echo '<center><img src="' . $files[$the_chosen_one] . '"'
            . (empty($file_sizes[$the_chosen_one]) ? '' : ' height="' . $file_sizes[$the_chosen_one] . '"')
            . '></center>';
      }
   }

}



/**
  * Get a remote image file
  *
  * @param array $remote_image_source An array of remote image
  *                                   sources.  Each source is
  *                                   a sub-array with the following
  *                                   elements:
  *                                      "address"  This is the location of
  *                                                 the web page that contains
  *                                                 the desired image (usually,
  *                                                 you want to pick web sites
  *                                                 that change the target
  *                                                 image frequently).
  *                                      "parse_pattern"  This is the regular
  *                                                       expression pattern
  *                                                       that is used to extract
  *                                                       only the image address
  *                                                       out of the web page.
  *                                      "pattern_group_number"  The "parse_pattern"
  *                                                              should capture the
  *                                                              needed image address
  *                                                              in a set of
  *                                                              parenthesis; this
  *                                                              element tells us
  *                                                              which set of
  *                                                              parenthesis it is.
  *                                      "image_address_prefix"  When images are given
  *                                                              as relative links in
  *                                                              a web page, this
  *                                                              element can be used
  *                                                              to prepend the website's
  *                                                              domain to it.  This is
  *                                                              OPTIONAL.
  *                                      "image_height"  If you'd like the target
  *                                                      image to be sized up or down,
  *                                                      you may specify a pixel height
  *                                                      here (width will be proportional).
  *                                                      If you want to display the image
  *                                                      at its original size, set this to
  *                                                      0 (zero) or just leave it out.
  *                                                      This is OPTIONAL.
  *                                      "cache_seconds"  This specifies how long you'd
  *                                                       like to keep this image
  *                                                       address cached.  This
  *                                                       prevents the plugin from
  *                                                       reading and parsing the web
  *                                                       page every time the login
  *                                                       page is loaded.  For images
  *                                                       that change daily, 86400
  *                                                       seconds is reasonable (24 hours).
  * @param boolean $verify_image_links Indicates whether or not remote image
  *                                    addresses should be verified (even when
  *                                    being cached) (turning verification off
  *                                    can increase performance on the login page)
  * @param int $preferences_key_size An integer that limits the size of user
  *                                  preferences keys (mostly useful when storing
  *                                  preferences in a database) (OPTIONAL; default 64)
  *
  * @return mixed A two-element array, where the first element is
  *               the address of the remote image (ready for use in the
  *               image tag's "src" attribute) and the second element is
  *               the image height to be used when rendering this
  *               particular image (ready for use in the image tag's
  *               "height" attribute).  When any error occurs that
  *               prevents image retrieval, FALSE will be returned.
  *
  */
function get_remote_image($remote_image_source, $verify_image_links,
                          $preferences_key_size=64)
{

   if (empty($remote_image_source['image_height']))
      $remote_image_source['image_height'] = 0;


   static $remote_image_cache = array();
   if (!empty($remote_image_cache[$remote_image_source['address']]))
   {

      // double check that the cached file is valid; if
      // not, fall through to retrieve the file normally
      //
      if (!$verify_image_links
       || ($SOCKET = sq_call_function_suppress_errors('fopen', array($remote_image_cache[$remote_image_source['address']], 'r'))))
      {
         @fclose($SOCKET);
         return array($remote_image_cache[$remote_image_source['address']],
                      $remote_image_source['image_height']);
      }
   }


   // if we are caching this file, check it now
   //
   if ($remote_image_source['cache_seconds'])
   {

      $cache_key = base64_encode($remote_image_source['address']);
      $preferences_key_size -= 5;  // for "date_" prefix
      if ($preferences_key_size && strlen($cache_key) > $preferences_key_size)
      {
         $cache_key = substr($cache_key, 0, (int)($preferences_key_size / 2))
                    . substr($cache_key, -(int)($preferences_key_size / 2));
      }

      global $prefs_dsn, $data_dir, $prefs_are_cached, $prefs_cache;

      // eiw - a bit messy, but we need to have prefs
      // functions here on the login page where they
      // are not usually needed - borrowed from init.php
      // and NOT tested with custom pref backends
      //
      if (check_sm_version(1, 5, 2))
      {
/* more recently, 1.5.2 fixed this issue......
         include_once(SM_PATH . 'functions/strings.php');
         include_once(SM_PATH . 'functions/prefs.php');
         $prefs_backend = do_hook('prefs_backend', $null);
         if (isset($prefs_backend) && !empty($prefs_backend) && file_exists(SM_PATH . $prefs_backend)) {
             include_once(SM_PATH . $prefs_backend);
         } elseif (isset($prefs_dsn) && !empty($prefs_dsn)) {
             include_once(SM_PATH . 'functions/db_prefs.php');
         } else {
             include_once(SM_PATH . 'functions/file_prefs.php');
         }
*/
      }
      else
         include_once(SM_PATH . 'functions/prefs.php');


      // check local files cache
      //
      // note how we unset the prefs cache flag before/after this
      // code so as not to corrupt any prefs for the user who is
      // currently logging in
      //
      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');

      $cached_file = getPref($data_dir, 'login_image', $cache_key, '');
      $cached_date = getPref($data_dir, 'login_iamge', 'date_' . $cache_key, '');

      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');


      // if cache is still in effect, return it
      //
      $now = time();
      if (!empty($cached_date) && $now - $cached_date <= $remote_image_source['cache_seconds'])
      {

         // double check that the cached file is valid; if
         // not, fall through to retrieve the file normally
         //
         if (!$verify_image_links
          || ($SOCKET = sq_call_function_suppress_errors('fopen', array($cached_file, 'r'))))
         {
            @fclose($SOCKET);
            return array($cached_file, $remote_image_source['image_height']);
         }

      }

   }


   // retrieve source page
   //
   if (!($SOCKET = sq_call_function_suppress_errors('fopen', array($remote_image_source['address'], 'r'))))
      return FALSE;

   $contents = '';
   while (!feof($SOCKET))
      $contents .= fread($SOCKET, 8192);

   @fclose($SOCKET);

   
   // we need a parse pattern
   //
   if (empty($remote_image_source['parse_pattern']))
      return FALSE;


   // parse out the image URI
   //
   preg_match($remote_image_source['parse_pattern'], $contents, $matches);
   if (empty($matches[$remote_image_source['pattern_group_number']]))
      return FALSE;


   if (!empty($remote_image_source['image_address_prefix']))
      $matches[$remote_image_source['pattern_group_number']] = $remote_image_source['image_address_prefix'] . $matches[$remote_image_source['pattern_group_number']];


   // double check that the cached file is valid; if
   // not, we have nothing to return
   //
   if ($verify_image_links
    && (!($SOCKET = sq_call_function_suppress_errors('fopen', array($matches[$remote_image_source['pattern_group_number']], 'r')))))
      return FALSE;
   @fclose($SOCKET);


   // store in local (static) cache
   //
   $remote_image_cache[$remote_image_source['address']] = $matches[$remote_image_source['pattern_group_number']];


   // if we are caching this file, store it
   //
   if ($remote_image_source['cache_seconds'])
   {
      setPref($data_dir, 'login_image', $cache_key, $matches[$remote_image_source['pattern_group_number']]);
      setPref($data_dir, 'login_image', 'date_' . $cache_key, $now);
               
      // clear prefs cache again
      //
      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');
   }


   return array($matches[$remote_image_source['pattern_group_number']],
                $remote_image_source['image_height']);

}



/**
  * Get list of local image files
  *
  * @param string $image_directory The path to the directory
  *                                containing the images (should
  *                                be relative to the SquirrelMail
  *                                "src/" directory)
  * @param array $ignore_list A list of file names to be ignored
  *                           (the file globbing characters * and ?
  *                           are acceptable herein)
  * @param int $cache_seconds The number of seconds to cache the
  *                           file list.  If zero, the list will
  *                           never be cached (OPTIONAL; default
  *                           is zero - don't cache).
  * @param string $directory_separator The character used to
  *                                    separate directories in
  *                                    a file path (OPTIONAL;
  *                                    default = '/')
  *
  * @return array A list of the image files with paths ready
  *               to be used in the "src" attribute to an
  *               image tag.  If no files exist or any errors
  *               occur, an empty list is returned.
  *
  */
function get_local_image_files($image_directory, $ignore_list,
                               $cache_seconds=0, $directory_separator='/')
{

   static $files = array();
   if (!empty($files)) return $files;


   // if we are caching the local file list, check it now
   //
   if ($cache_seconds)
   {

      global $prefs_dsn, $data_dir, $prefs_are_cached, $prefs_cache;

      // eiw - a bit messy, but we need to have prefs
      // functions here on the login page where they
      // are not usually needed - borrowed from init.php
      // and NOT tested with custom pref backends
      //
      if (check_sm_version(1, 5, 2))
      {
/* more recently, 1.5.2 fixed this issue......
         include_once(SM_PATH . 'functions/strings.php');
         include_once(SM_PATH . 'functions/prefs.php');
         $prefs_backend = do_hook('prefs_backend', $null);
         if (isset($prefs_backend) && !empty($prefs_backend) && file_exists(SM_PATH . $prefs_backend)) {
             include_once(SM_PATH . $prefs_backend);
         } elseif (isset($prefs_dsn) && !empty($prefs_dsn)) {
             include_once(SM_PATH . 'functions/db_prefs.php');
         } else {
             include_once(SM_PATH . 'functions/file_prefs.php');
         }
*/
      }
      else
         include_once(SM_PATH . 'functions/prefs.php');


      // check local files cache
      //
      // note how we unset the prefs cache flag before/after this
      // code so as not to corrupt any prefs for the user who is
      // currently logging in
      //
      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');

      $cached_files = getPref($data_dir, 'login_image', 'local_files', '');
      $cached_date = getPref($data_dir, 'login_iamge', 'local_files_date', '');

      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');


      // if cache is still in effect, return it
      //
      $now = time();
      if (!empty($cached_date) && $now - $cached_date <= $cache_seconds)
      {
         $files = explode('|##|', $cached_files);
         return $files;
      }

   }


   // make sure image directory path ends with a directory separator
   //
   if ($image_directory[strlen($image_directory) - 1] != $directory_separator)
      $image_directory = $image_directory . $directory_separator;


   if (!($DIR = opendir($image_directory))) return array();


   // prepare the ignore list (convert glob characters to regular
   // expression style)
   //
   // note we have to first quote any special regular expression 
   // characters, and then UN-quote the * and ?, since they are
   // special to us in a different way (glob wildcards)
   //
   static $ignore_list_regular_expression_style = array();
   if (empty($ignore_list_regular_expression_style))
   {
      foreach ($ignore_list as $ignore)
      {
         $ignore_list_regular_expression_style[] = '/^' . str_replace(array('*', '?'), array('.*', '.'), str_replace(array('\\*', '\\?'), array('*', '?'), preg_quote($ignore, '/'))) . '$/i';
      }
   }


   // now gather all the needed files
   //
   while (($file = readdir($DIR)) !== FALSE)
   {
      foreach ($ignore_list_regular_expression_style as $ignore)
         if (preg_match($ignore, $file)) continue 2;

      $files[] = $image_directory . $file;
   }


   closedir($DIR);


   // if we are caching the local file list, store it
   //
   if ($cache_seconds)
   {
      setPref($data_dir, 'login_image', 'local_files', implode('|##|', $files));
      setPref($data_dir, 'login_image', 'local_files_date', $now);
               
      // clear prefs cache again
      //
      $prefs_are_cached = FALSE;
      $prefs_cache = FALSE;
      sqsession_unregister('prefs_are_cached');
      sqsession_unregister('prefs_cache');
   }


   return $files;

}



