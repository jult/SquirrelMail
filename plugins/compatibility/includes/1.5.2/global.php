<?php



// Exmple of how to include a function that was added in two 
// different versions, each in a different release series - in
// this example, a function called "sq_new_function()" that was
// added in both 1.5.2 and 1.4.10
//
/*
if ((!compatibility_check_sm_version(1, 4, 10)
 || (compatibility_check_sm_version(1, 5, 0) && !compatibility_check_sm_version(1, 5, 2)))
 && !function_exists('sq_new_function'))
{
function sq_new_function() 
{
   echo "HELLO WORLD";
}
}
*/



// constants added in version 1.5.2
//
define('SM_DEBUG_MODE_OFF', 0);             // complete error suppression
define('SM_DEBUG_MODE_SIMPLE', 1);          // PHP E_ERROR
define('SM_DEBUG_MODE_MODERATE', 512);      // PHP E_ALL
define('SM_DEBUG_MODE_ADVANCED', 524288);   // PHP E_ALL plus log errors intentionally suppressed
define('SM_DEBUG_MODE_STRICT', 536870912);  // PHP E_STRICT



//
// taken from functions/global.php on 2007/06/23
// since 1.5.2
//
if (!function_exists('list_files'))
{
function list_files($directory_path, $extensions='', $return_filenames_only=TRUE,
                    $include_directories=TRUE, $directories_only=FALSE,
                    $separate_files_and_directories=FALSE, $only_sm=TRUE) {

    $files = array();
    $directories = array();


    // make sure requested path is under SM_PATH if needed
    //
    if ($only_sm) {
        if (strpos(realpath($directory_path), realpath(SM_PATH)) !== 0) {
            //plain_error_message(_("Illegal filesystem access was requested"));
            echo _("Illegal filesystem access was requested");
            exit;
        }
    }


    // validate given directory
    //
    if (empty($directory_path)
     || !is_dir($directory_path)
     || !($DIR = opendir($directory_path))) {
        return $files;
    }


    // ensure extensions is an array and is properly formatted
    //
    if (!empty($extensions)) {
        if (!is_array($extensions))
            $extensions = explode(',', $extensions);
        $temp_extensions = array();
        foreach ($extensions as $ext)
            $temp_extensions[] = '.' . trim(trim($ext), '.');
        $extensions = $temp_extensions;
    } else $extensions = array();


    $directory_path = rtrim($directory_path, '/');


    // parse through the files
    //
    while (($file = readdir($DIR)) !== false) {

        if ($file == '.' || $file == '..') continue;

        if (!empty($extensions))
            foreach ($extensions as $ext)
                if (strrpos($file, $ext) !== (strlen($file) - strlen($ext)))
                    continue 2;

        // only use is_dir() if we really need to (be as efficient as possible)
        //
        $is_dir = FALSE;
        if (!$include_directories || $directories_only
                                  || $separate_files_and_directories) {
            if (is_dir($directory_path . '/' . $file)) {
                if (!$include_directories) continue;
                $is_dir = TRUE;
                $directories[] = ($return_filenames_only
                               ? $file
                               : $directory_path . '/' . $file);
            }
            if ($directories_only) continue;
        }

        if (!$separate_files_and_directories
         || ($separate_files_and_directories && !$is_dir)) {
            $files[] = ($return_filenames_only
                     ? $file
                     : $directory_path . '/' . $file);
        }

    }
    closedir($DIR);


    if ($directories_only) return $directories;
    if ($separate_files_and_directories) return array('FILES' => $files,
                                                      'DIRECTORIES' => $directories);
    return $files;

}
}



//
// taken from functions/plugin.php on 2007/04/05
// since 1.5.2
//
if (!function_exists('get_plugin_version'))
{
function get_plugin_version($plugin_name, $force_inclusion = FALSE, $do_parse = FALSE)
{

   $info_function = $plugin_name . '_info';
   $version_function = $plugin_name . '_version';
   $plugin_info = array();
   $plugin_version = FALSE;


   // first attempt to find the plugin info function, wherein
   // the plugin version should be available
   //
   if (function_exists($info_function))
      $plugin_info = $info_function();
   else if ($force_inclusion
    && file_exists(SM_PATH . 'plugins/' . $plugin_name . '/setup.php'))
   {

      /* --- Old code, keeping just in case... problem with it is, for example,
         if it is used, but later we are checking if the same plugin is
         activated (because it SHOULD be), this code having run will possibly
         create a false positive.
      include_once(SM_PATH . 'plugins/' . $plugin_name . '/setup.php');
      if (function_exists($info_function))
         $plugin_info = $info_function();
      --- */

      // so what we need to do is process this plugin without
      // it polluting our environment
      //
      // we *could* just use the above code, which is more of a
      // sure thing than some regular expressions, and then test
      // the contents of the $plugins array to see if this plugin
      // is actually activated, and that might be good enough, but
      // for now, we'll use the following approach, because of two
      // concerns: other plugins and other templates might force
      // the inclusion of a plugin (which SHOULD also add it to
      // the $plugins array, but am not 100% sure at this time (FIXME)),
      // and because the regexps below should work just fine with
      // any resonably formatted plugin setup file.
      //
      // read the target plugin's setup.php file into a string,
      // then use a regular expression to try to find the version...
      // this of course can break if plugin authors do funny things
      // with their file formatting
      //
      $setup_file = '';
      $file_contents = file(SM_PATH . 'plugins/' . $plugin_name . '/setup.php');
      foreach ($file_contents as $line)
         $setup_file .= $line;


      // this regexp grabs a version number from a standard
      // <plugin>_info() function
      //
      if (preg_match('/[\'"]version[\'"]\s*=>\s*[\'"](.+?)[\'"]/is', $setup_file, $matches))
         $plugin_info = array('version' => $matches[1]);


      // this regexp grabs a version number from a standard
      // (deprecated) <plugin>_version() function
      //
      else if (preg_match('/function\s+.*?' . $plugin_name . '_version.*?\(.*?\).*?\{.*?return\s+[\'"](.+?)[\'"]/is', $setup_file, $matches))
         $plugin_info = array('version' => $matches[1]);

   }
   if (!empty($plugin_info['version']))
      $plugin_version = $plugin_info['version'];


   // otherwise, look for older version function
   //
   if (!$plugin_version && function_exists($version_function))
       $plugin_version = $version_function();


   if ($plugin_version && $do_parse)
   {

      // massage version number into something we understand
      //
      // the first regexp strips everything and anything that follows
      // the first occurance of a non-digit (or non decimal point), so
      // beware that putting letters in the middle of a version string
      // will effectively truncate the version string right there (but
      // this also just helps remove the SquirrelMail version part off
      // of versions such as "1.2.3-1.4.4")
      //
      // the second regexp just strips out non-digits/non-decimal points
      // (and might be redundant(?))
      //
      // the regexps are wrapped in a trim that makes sure the version
      // does not start or end with a decimal point
      //
      $plugin_version = trim(preg_replace(array('/[^0-9.]+.*$/', '/[^0-9.]/'),
                                          '', $plugin_version),
                             '.');

   }

   return $plugin_version;

}
}



//
// taken from functions/plugin.php on 2007/04/05
// since 1.5.2
//
if (!function_exists('check_plugin_version'))
{
function check_plugin_version($plugin_name,
                              $a = 0, $b = 0, $c = 0,
                              $force_inclusion = FALSE)
{

   $plugin_version = get_plugin_version($plugin_name, $force_inclusion, TRUE);
   if (!$plugin_version) return FALSE;


   // split the version string into sections delimited by
   // decimal points, and make sure we have three sections
   //
   $plugin_version = explode('.', $plugin_version);
   if (!isset($plugin_version[0])) $plugin_version[0] = 0;
   if (!isset($plugin_version[1])) $plugin_version[1] = 0;
   if (!isset($plugin_version[2])) $plugin_version[2] = 0;
//   sm_print_r($plugin_version);


   // now test the version number
   //
   if ($plugin_version[0] < $a ||
      ($plugin_version[0] == $a && $plugin_version[1] < $b) ||
      ($plugin_version[0] == $a && $plugin_version[1] == $b && $plugin_version[2] < $c))
         return FALSE;


   return TRUE;

}
}



//
// taken from functions/plugin.php on 2008/07/22
// since 1.5.2
//
if (!function_exists('get_plugin_requirement'))
{
function get_plugin_requirement($plugin_name, $requirement,
                                $ignore_incompatible = TRUE,
                                $force_inclusion = FALSE)
{

   $info_function = $plugin_name . '_info';
   $plugin_info = array();
   $requirement_value = NULL;


   // first attempt to find the plugin info function, wherein
   // the plugin requirements should be available
   //
   if (function_exists($info_function))
      $plugin_info = $info_function();
   else if ($force_inclusion
    && file_exists(SM_PATH . 'plugins/' . $plugin_name . '/setup.php'))
   {

      /* --- Old code, keeping just in case... problem with it is, for example,
         if it is used, but later we are checking if the same plugin is
         activated (because it SHOULD be), this code having run will possibly
         create a false positive.
      include_once(SM_PATH . 'plugins/' . $plugin_name . '/setup.php');
      if (function_exists($info_function))
         $plugin_info = $info_function();
      --- */

      // so what we need to do is process this plugin without
      // it polluting our environment
      //
      // we *could* just use the above code, which is more of a
      // sure thing than a regular expression, and then test
      // the contents of the $plugins array to see if this plugin
      // is actually activated, and that might be good enough, but
      // for now, we'll use the following approach, because of two
      // concerns: other plugins and other templates might force
      // the inclusion of a plugin (which SHOULD also add it to
      // the $plugins array, but am not 100% sure at this time (FIXME)),
      // and because the regexp below should work just fine with
      // any resonably formatted plugin setup file.
      //
      // read the target plugin's setup.php file into a string,
      // then use a regular expression to try to find the needed
      // requirement information...
      // this of course can break if plugin authors do funny things
      // with their file formatting
      //
      $setup_file = '';
      $file_contents = file(SM_PATH . 'plugins/' . $plugin_name . '/setup.php');
      foreach ($file_contents as $line)
         $setup_file .= $line;


      // this regexp grabs the full plugin info array from a standard
      // <plugin>_info() function... determining the end of the info
      // array can fail, but if authors end the array with ");\n"
      // (without quotes), then it should work well, especially because
      // newlines shouldn't be found inside the array after any ");"
      // (without quotes)
      //
      if (preg_match('/function\s+.*?' . $plugin_name . '_info.*?\(.*?\).*?\{.*?(array.+?\)\s*;)\s*' . "\n" . '/is', $setup_file, $matches))
         eval('$plugin_info = ' . $matches[1]);

   }


   // attempt to get the requirement from the "global" scope
   // of the plugin information array
   //
   if (isset($plugin_info[$requirement])
    && !is_null($plugin_info[$requirement]))
      $requirement_value = $plugin_info[$requirement];


   // now, if there is a series of per-version requirements,
   // check there too
   //
   if (!empty($plugin_info['per_version_requirements'])
    && is_array($plugin_info['per_version_requirements']))
   {

      // iterate through requirements, where keys are version
      // numbers -- tricky part is knowing the difference between
      // more than one version for which the current SM installation
      // passes the check_sm_version() test... we want the highest one
      //
      $requirement_value_override = NULL;
      $highest_version_array = array();
      foreach ($plugin_info['per_version_requirements'] as $version => $requirement_overrides)
      {

         $version_array = explode('.', $version);
         if (sizeof($version_array) != 3) continue;

         $a = $version_array[0];
         $b = $version_array[1];
         $c = $version_array[2];

         // complicated way to say we are interested in these overrides
         // if the version is applicable to us and if the overrides include
         // the requirement we are looking for, or if the plugin is not
         // compatible with this version of SquirrelMail (unless we are
         // told to ignore such)
         //
         if (check_sm_version($a, $b, $c)
          && ((!$ignore_incompatible
            && (!empty($requirement_overrides[SQ_INCOMPATIBLE])
             || $requirement_overrides === SQ_INCOMPATIBLE))
           || (is_array($requirement_overrides)
            && isset($requirement_overrides[$requirement])
            && !is_null($requirement_overrides[$requirement]))))
         {

            if (empty($highest_version_array)
             || $highest_version_array[0] < $a
             || ($highest_version_array[0] == $a
             && $highest_version_array[1] < $b)
             || ($highest_version_array[0] == $a
             && $highest_version_array[1] == $b
             && $highest_version_array[2] < $c))
            {
               $highest_version_array = $version_array;
               if (!empty($requirement_overrides[SQ_INCOMPATIBLE])
                || $requirement_overrides === SQ_INCOMPATIBLE)
                  $requirement_value_override = SQ_INCOMPATIBLE;
               else
                  $requirement_value_override = $requirement_overrides[$requirement];
            }

         }

      }

      // now grab override if one is available
      //
      if (!is_null($requirement_value_override))
         $requirement_value = $requirement_value_override;

   }

   return $requirement_value;

}
}



//
// taken from functions/plugin.php on 2007/06/29
// since 1.5.2
//
if (!function_exists('get_plugin_dependencies'))
{
function get_plugin_dependencies($plugin_name, $force_inclusion = FALSE,
                                 $do_parse = TRUE)
{

   $plugin_dependencies = get_plugin_requirement($plugin_name,
                                                 'required_plugins',
                                                 $force_inclusion);

   // the plugin is simply incompatible, no need to continue here
   //
   if ($plugin_dependencies === SQ_INCOMPATIBLE)
      return $plugin_dependencies;


   // not an array of requirements?  wrong format, just return FALSE
   //
   if (!is_array($plugin_dependencies))
      return FALSE;


   // make sure everything is in order...
   //
   if (!empty($plugin_dependencies))
   {

      $new_plugin_dependencies = array();
      foreach ($plugin_dependencies as $plugin_name => $plugin_requirements)
      {

         // if $plugin_requirements isn't an array, this is old-style,
         // where only the version number was given...
         //
         if (is_string($plugin_requirements))
            $plugin_requirements = array('version' => $plugin_requirements,
                                         'activate' => FALSE);


         // trap badly formatted requirements arrays that don't have
         // needed info
         //
         if (!is_array($plugin_requirements)
          || !isset($plugin_requirements['version']))
            continue;
         if (!isset($plugin_requirements['activate']))
            $plugin_requirements['activate'] = FALSE;


         // parse version into something we understand?
         //
         if ($do_parse)
         {

            // massage version number into something we understand
            //
            // the first regexp strips everything and anything that follows
            // the first occurance of a non-digit (or non decimal point), so
            // beware that putting letters in the middle of a version string
            // will effectively truncate the version string right there (but
            // this also just helps remove the SquirrelMail version part off
            // of versions such as "1.2.3-1.4.4")
            //
            // the second regexp just strips out non-digits/non-decimal points
            // (and might be redundant(?))
            //
            // the regexps are wrapped in a trim that makes sure the version
            // does not start or end with a decimal point
            //
            $plugin_requirements['version']
               = trim(preg_replace(array('/[^0-9.]+.*$/', '/[^0-9.]/'),
                                   '', $plugin_requirements['version']),
                                   '.');

         }

         $new_plugin_dependencies[$plugin_name] = $plugin_requirements;

      }

      $plugin_dependencies = $new_plugin_dependencies;

   }

   return $plugin_dependencies;

}
}



//
// taken from functions/plugin.php on 2007/06/29
// since 1.5.2
//
if (!function_exists('check_plugin_dependencies'))
{
function check_plugin_dependencies($plugin_name, $force_inclusion = FALSE)
{

   $dependencies = get_plugin_dependencies($plugin_name, $force_inclusion);
   if (!$dependencies) return TRUE;
   if ($dependencies === SQ_INCOMPATIBLE) return $dependencies;
   $missing_or_bad = array();

   foreach ($dependencies as $depend_name => $depend_requirements)
   {

      // check for core plugins first
      //
      if (strpos(strtoupper($depend_requirements['version']), 'CORE') === 0)
      {

         // see if the plugin is in the core (just check if the directory exists)
         //
         if (!file_exists(SM_PATH . 'plugins/' . $depend_name))
            $missing_or_bad[$depend_name] = $depend_requirements;


         // check if it is activated if need be
         //
         else if ($depend_requirements['activate'] && !is_plugin_enabled($depend_name))
            $missing_or_bad[$depend_name] = $depend_requirements;


         // check if this is the right core version if one is given
         // (note this is pretty useless - a plugin should specify
         // whether or not it itself is compatible with this version
         // of SM in the first place)
         //
         else if (strpos($depend_requirements['version'], ':') !== FALSE)
         {
            $version = explode('.', substr($depend_requirements['version'], strpos($depend_requirements['version'], ':') + 1), 3);
            $version[0] = intval($version[0]);
            if (isset($version[1])) $version[1] = intval($version[1]);
            else $version[1] = 0;
            if (isset($version[2])) $version[2] = intval($version[2]);
            else $version[2] = 0;

            if (!check_sm_version($version[0], $version[1], $version[2]))
               $missing_or_bad[$depend_name] = $depend_requirements;
         }

         continue;

      }

      // if the plugin is actually incompatible; check that it
      // is not activated
      //
      if ($depend_requirements['version'] == SQ_INCOMPATIBLE)
      {

         if (is_plugin_enabled($depend_name))
            $missing_or_bad[$depend_name] = $depend_requirements;

         continue;

      }

      // check for normal plugins
      //
      $version = explode('.', $depend_requirements['version'], 3);
      $version[0] = intval($version[0]);
      if (isset($version[1])) $version[1] = intval($version[1]);
      else $version[1] = 0;
      if (isset($version[2])) $version[2] = intval($version[2]);
      else $version[2] = 0;

      $force_dependency_inclusion = !$depend_requirements['activate'];

      if (!check_plugin_version($depend_name, $version[0], $version[1],
                                $version[2], $force_dependency_inclusion))
         $missing_or_bad[$depend_name] = $depend_requirements;
   }

   if (empty($missing_or_bad)) return TRUE;


   // get non-parsed required versions
   //
   $non_parsed_dependencies = get_plugin_dependencies($plugin_name,
                                                      $force_inclusion,
                                                      FALSE);
   $return_array = array();
   foreach ($missing_or_bad as $depend_name => $ignore)
      $return_array[$depend_name] = $non_parsed_dependencies[$depend_name];

   return $return_array;

}
}



//
// taken from functions/plugin.php on 2006/09/21
// since 1.5.2
//
if (!function_exists('sqm_array_merge'))
{
function sqm_array_merge($a, $b, $concat_strings=true) {

    $ret = array();

    if (is_array($a)) {
        $ret = $a;
    } else {
        if (is_string($a) && is_string($b) && $concat_strings) {
            return $a . $b;
        }
        $ret[] = $a;
    }


    if (is_array($b)) {
        foreach ($b as $key => $value) {
            if (isset($ret[$key])) {
                $ret[$key] = sqm_array_merge($ret[$key], $value, $concat_strings);
            } else {
                $ret[$key] = $value;
            }
        }
    } else {
        $ret[] = $b;
    }

    return $ret;

}
}



//
// taken from functions/global.php on 2007/01/14
// since 1.5.2
//
if (!function_exists('sqGetGlobalVarMultiple'))
{
function sqGetGlobalVarMultiple($name, &$value, $indicator_field,
                                $search = SQ_INORDER,
                                $fallback_no_suffix=TRUE, $default=NULL,
                                $typecast=FALSE) {

    // Set arbitrary max limit -- should be much lower except on the
    // search results page, if there are many (50 or more?) mailboxes
    // shown, this may not be high enough.  Is there some way we should
    // automate this value?
    //
    $max_form_search = 100;

    for ($i = 1; $i <= $max_form_search; $i++) {
        if (sqGetGlobalVar($indicator_field . '_' . $i, $temp, $search)) {
            return sqGetGlobalVar($name . '_' . $i, $value, $search, $default, $typecast);
        }
    }


    // no indicator field found; just try without suffix if allowed
    //
    if ($fallback_no_suffix) {
        return sqGetGlobalVar($name, $value, $search, $default, $typecast);
    }


    // no dice, set default and return FALSE
    //
    if (!is_null($default)) {
        $value = $default;
    }
    return FALSE;

}
}



//
// taken from functions/global.php on 2007/02/05
// since 1.5.2
//
if (!function_exists('sq_htmlspecialchars'))
{
function sq_htmlspecialchars($value, $quote_style=ENT_QUOTES) {

    if ($quote_style === FALSE) $quote_style = ENT_QUOTES;

    // array?  go recursive...
    //
    if (is_array($value)) {
        $return_array = array();
        foreach ($value as $key => $val) {
            $return_array[sq_htmlspecialchars($key, $quote_style)]
                = sq_htmlspecialchars($val, $quote_style);
        }
        return $return_array;

    // sanitize strings only
    //
    } else if (is_string($value)) {
        if ($quote_style === TRUE)
            return str_replace(array('\'', '"'), array('&#039;', '&quot;'), $value);
        else
            return htmlspecialchars($value, $quote_style);
    }

    // anything else gets returned with no changes
    //
    return $value;

}
}



//
// taken from 1.4.10-svn functions/i18n.php on 2007/03/30
// since 1.5.2 and 1.4.10
//
// This code was taken from 1.4.10, because it has code that
// is needed in the 1.4.x series but will still work (albiet
// a spec more inefficient) in 1.5.x.  If you are running 1.5.x
// you should be running 1.5.2+, where this function is natively
// included anyway.
//
if ((!compatibility_check_sm_version(1, 4, 10)
 || (compatibility_check_sm_version(1, 5, 0) && !compatibility_check_sm_version(1, 5, 2)))
 && !function_exists('sq_change_text_domain'))
{
function sq_change_text_domain($domain_name, $directory='') {

    global $use_gettext;
    static $domains_already_seen = array();
    $return_value = textdomain(NULL);

    // empty domain defaults to "squirrelmail"
    //
    if (empty($domain_name)) $domain_name = 'squirrelmail';

    // only need to call bindtextdomain() once unless
    // $use_gettext is turned on
    //
    if (!$use_gettext && in_array($domain_name, $domains_already_seen)) {
        textdomain($domain_name);
        return $return_value;
    }

    $domains_already_seen[] = $domain_name;

    if (empty($directory)) $directory = SM_PATH . 'locale/';

    sq_bindtextdomain($domain_name, $directory);
    textdomain($domain_name);

    return $return_value;
}
}



//
// taken from 1.4.12-svn functions/global.php on 2007/12/17
// since 1.5.2 and 1.4.12
//
// This code was taken from 1.4.12, because the 1.5.2 version
// of this function has constants that are not defined in the 
// 1.4.x series.  If you are running 1.5.x, you should be 
// running 1.5.2+, where this function is natively included 
// anyway.
//
if ((!compatibility_check_sm_version(1, 4, 12)
 || (compatibility_check_sm_version(1, 5, 0) && !compatibility_check_sm_version(1, 5, 2)))
 && !function_exists('sq_call_function_suppress_errors'))
{
function sq_call_function_suppress_errors($function, $args=array()) {
   $display_errors = ini_get('display_errors');
   ini_set('display_errors', '0');
   $ret = call_user_func_array($function, $args);
   ini_set('display_errors', $display_errors);
   return $ret;
}
}



//
// taken from functions/global.php on 2008/01/12
// since 1.5.2
//
if (!function_exists('get_secured_config_value'))
{
function get_secured_config_value($var_name) {

    static $return_values = array();

    // if we can avoid it, return values that have
    // already been retrieved (so we don't have to
    // include the config file yet again)
    //
    if (isset($return_values[$var_name])) {
        return $return_values[$var_name];
    }


    // load site configuration
    //
    require(SM_PATH . 'config/config.php');

    // load local configuration overrides
    //
    if (file_exists(SM_PATH . 'config/config_local.php')) {
        require(SM_PATH . 'config/config_local.php');
    }

    // if SM isn't in "secured configuration" mode,
    // just return the desired value from the global scope
    //
    if (!$secured_config) {
        global $$var_name;
        $return_values[$var_name] = $$var_name;
        return $$var_name;
    }

    // else we return what we got from the config file
    //
    $return_values[$var_name] = $$var_name;
    return $$var_name;

}
}



//
// taken from functions/compose.php on 2008/02/29
// since 1.5.2
//
if (!function_exists('sq_send_mail'))
{
function sq_send_mail($to, $subject, $body, $from, $cc='', $bcc='', $message='')
{

   require_once(SM_PATH . 'functions/mime.php');
   require_once(SM_PATH . 'class/mime.class.php');

   if (empty($message))
   {
      $message = new Message();
      $header  = new Rfc822Header();

      $message->setBody($body);
      $content_type = new ContentType('text/plain');
      global $special_encoding, $default_charset;
      if ($special_encoding)
         $rfc822_header->encoding = $special_encoding;
      else
         $rfc822_header->encoding = '8bit';
      if ($default_charset)
         $content_type->properties['charset']=$default_charset;
      $header->content_type = $content_type;

      $header->parseField('To', $to);
      $header->parseField('Cc', $cc);
      $header->parseField('Bcc', $bcc);
      $header->parseField('From', $from);
      $header->parseField('Subject', $subject);
      $message->rfc822_header = $header;
   }
//sm_print_r($message);exit;


   global $useSendmail;


   // ripped from src/compose.php - based on both 1.5.2 and 1.4.14
   //
   if (!$useSendmail) {
      require_once(SM_PATH . 'class/deliver/Deliver_SMTP.class.php');
      $deliver = new Deliver_SMTP();
      global $smtpServerAddress, $smtpPort, $pop_before_smtp,
             $domain, $pop_before_smtp_host;

      $authPop = (isset($pop_before_smtp) && $pop_before_smtp) ? true : false;
      if (empty($pop_before_smtp_host)) $pop_before_smtp_host = $smtpServerAddress;
      $user = '';
      $pass = '';
      get_smtp_user($user, $pass);
      $stream = $deliver->initStream($message,$domain,0,
                $smtpServerAddress, $smtpPort, $user, $pass, $authPop, $pop_before_smtp_host);
   } else {
      require_once(SM_PATH . 'class/deliver/Deliver_SendMail.class.php');
      global $sendmail_path, $sendmail_args;
      // Check for outdated configuration
      if (!isset($sendmail_args)) {
         if ($sendmail_path=='/var/qmail/bin/qmail-inject') {
            $sendmail_args = '';
         } else {
            $sendmail_args = '-i -t';
         }
      }
      $deliver = new Deliver_SendMail(array('sendmail_args'=>$sendmail_args));
      $stream = $deliver->initStream($message,$sendmail_path);
   }


   $success = false;
   $message_id = '';
   if ($stream) {
      $deliver->mail($message, $stream);
      if (!empty($message->rfc822_header->message_id)) {
         $message_id = $message->rfc822_header->message_id;
      }

      $success = $deliver->finalizeStream($stream);
   }

   return array($success, $message_id);

}
}



//
// taken from functions/html.php on 2008/05/21
// since 1.5.2
//
if (!function_exists('set_uri_vars'))
{
function set_uri_vars($uri, $values, $sanitize=TRUE) {
    foreach ($values as $key => $value)
        if (is_array($value)) {
          $i = 0;
          foreach ($value as $val)
             $uri = set_url_var($uri, $key . '[' . $i++ . ']', $val, $sanitize);
        }
        else
          $uri = set_url_var($uri, $key, $value, $sanitize);
    return $uri;
}
}



//
// taken from 1.5.2-svn functions/global.php on 2008/11/26
// since 1.5.2 and 1.4.17
//
if ((!compatibility_check_sm_version(1, 4, 17)
 || (compatibility_check_sm_version(1, 5, 0) && !compatibility_check_sm_version(1, 5, 2)))
 && !function_exists('is_ssl_secured_connection'))
{
function is_ssl_secured_connection()
{
    global $sq_ignore_http_x_forwarded_headers, $sq_https_port;
    $https_env_var = getenv('HTTPS');
    if ($sq_ignore_http_x_forwarded_headers
     || !sqgetGlobalVar('HTTP_X_FORWARDED_PROTO', $forwarded_proto, SQ_SERVER))
        $forwarded_proto = '';
    if (empty($sq_https_port)) // won't work with port 0 (zero)
       $sq_https_port = 443;
    if ((isset($https_env_var) && strcasecmp($https_env_var, 'on') === 0)
     || (sqgetGlobalVar('HTTPS', $https, SQ_SERVER) && !empty($https)
      && strcasecmp($https, 'off') !== 0)
     || (strcasecmp($forwarded_proto, 'https') === 0)
     || (sqgetGlobalVar('SERVER_PORT', $server_port, SQ_SERVER)
      && $server_port == $sq_https_port))
        return TRUE;
    return FALSE;
}
global $is_secure_connection;
$is_secure_connection = is_ssl_secured_connection();
}



//
// taken from 1.5.2-svn functions/files.php on 2008/11/26
// since 1.5.2
//
if (!function_exists('sq_is_writable'))
{
global $server_os;
if (DIRECTORY_SEPARATOR == '\\') $server_os = 'windows'; else $server_os = '*nix';
function sq_is_writable($path) {          
  
   global $server_os;
  
  
   // under *nix with safe_mode off, use the native is_writable()
   //                                              
   if ($server_os == '*nix' && !(bool)ini_get('safe_mode'))
      return is_writable($path);                   
  
  
   // if it's a directory, that means we have to create a temporary
   // file therein                                 
   //                                              
   $delete_temp_file = FALSE;             
   if (@is_dir($path) && ($temp_filename = @sq_create_tempfile($path)))
   {                                      
      $path .= DIRECTORY_SEPARATOR . $temp_filename;
      $delete_temp_file = TRUE;           
   }
  
  
   // try to open the file for writing (without trying to create it)
   //
   if (!@is_dir($path) && ($FILE = @fopen($path, 'r+')))
   {
      @fclose($FILE);

      // delete temp file if needed
      //
      if ($delete_temp_file)
         @unlink($path);

      return TRUE;
   }


   // delete temp file if needed
   //
   if ($delete_temp_file)
      @unlink($path);

   return FALSE;

}
}



//
// taken from 1.5.2-svn functions/files.php on 2008/11/26
// since 1.5.2
//
if (!function_exists('sq_create_tempfile'))
{
function sq_create_tempfile($directory)
{

    // give up after 1000 tries
    $maximum_tries = 1000;

    // using PHP >= 4.3.2 we can be truly atomic here
    $filemods = check_php_version(4, 3, 2) ? 'x' : 'w';

    for ($try = 0; $try < $maximum_tries; ++$try) {

        $localfilename = GenerateRandomString(32, '', 7);
        $full_localfilename = $directory . DIRECTORY_SEPARATOR . $localfilename;

        // filename collision. try again
        if ( file_exists($full_localfilename) ) {
            continue;
        }

        // try to open for (binary) writing
        $fp = @fopen( $full_localfilename, $filemods);

        if ($fp !== FALSE) {
            // success! make sure it's not readable, close and return filename
            chmod($full_localfilename, 0600);
            fclose($fp);
            return $localfilename;
        }

    }

    // we tried as many times as we could but didn't succeed.
    return FALSE;

}
}



//
// taken from 1.5.2-svn functions/global.php on 2008/12/04
// since 1.5.2
//
if (!function_exists('get_process_owner_info'))
{
function get_process_owner_info()
{
    if (!function_exists('posix_getuid'))
        return FALSE;

    $process_info['uid'] = posix_getuid();
    $process_info['euid'] = posix_geteuid();
    $process_info['gid'] = posix_getgid();
    $process_info['egid'] = posix_getegid();

    $user_info = posix_getpwuid($process_info['uid']);
    $euser_info = posix_getpwuid($process_info['euid']);
    $group_info = posix_getgrgid($process_info['gid']);
    $egroup_info = posix_getgrgid($process_info['egid']);

    $process_info['name'] = $user_info['name'];
    $process_info['ename'] = $euser_info['name'];
    $process_info['group'] = $user_info['name'];
    $process_info['egroup'] = $euser_info['name'];

    return $process_info;
}
}



//
// taken from 1.5.2-svn/1.4.20-svn (code is identical)
// functions/strings.php on 2009/12/06
// since 1.5.2 and 1.4.20-RC 1
//
if ((!compatibility_check_sm_version(1, 4, 20)
 || (compatibility_check_sm_version(1, 5, 0) && !compatibility_check_sm_version(1, 5, 2)))
 && !function_exists('sm_get_user_security_tokens'))
{
function sm_get_user_security_tokens($purge_old=TRUE)
{

   global $data_dir, $username, $max_token_age_days;

   $tokens = getPref($data_dir, $username, 'security_tokens', '');
   if (($tokens = unserialize($tokens)) === FALSE || !is_array($tokens))
      $tokens = array();

   // purge old tokens if necessary
   //
   if ($purge_old)
   {
      if (empty($max_token_age_days)) $max_token_age_days = 30;
      $now = time();
      $discard_token_date = $now - ($max_token_age_days * 86400);
      $cleaned_tokens = array();
      foreach ($tokens as $token => $timestamp)
         if ($timestamp >= $discard_token_date)
            $cleaned_tokens[$token] = $timestamp;
      $tokens = $cleaned_tokens;
   }

   return $tokens;

}
}



//
// taken from 1.5.2-svn/1.4.20-svn (code is identical)
// functions/strings.php on 2009/12/06
// since 1.5.2 and 1.4.20-RC 1
//
if ((!compatibility_check_sm_version(1, 4, 20)
 || (compatibility_check_sm_version(1, 5, 0) && !compatibility_check_sm_version(1, 5, 2)))
 && !function_exists('sm_generate_security_token'))
{
function sm_generate_security_token()
{

   global $data_dir, $username, $disable_security_tokens;
   $max_generation_tries = 1000;

   $tokens = sm_get_user_security_tokens();

   $new_token = GenerateRandomString(12, '', 7);
   $count = 0;
   while (isset($tokens[$new_token]))
   {
      $new_token = GenerateRandomString(12, '', 7);
      if (++$count > $max_generation_tries)
      {
         logout_error(_("Fatal token generation error; please contact your system administrator or the SquirrelMail Team"));
         exit;
      }
   }

   // is the token system enabled?  CAREFUL!
   //
   if (!$disable_security_tokens)
   {
      $tokens[$new_token] = time();
      setPref($data_dir, $username, 'security_tokens', serialize($tokens));
   }

   return $new_token;

}
}



//
// taken from 1.5.2-svn/1.4.20-svn (code is identical)
// functions/strings.php on 2009/12/06
// since 1.5.2 and 1.4.20-RC 1
//
if ((!compatibility_check_sm_version(1, 4, 20)
 || (compatibility_check_sm_version(1, 5, 0) && !compatibility_check_sm_version(1, 5, 2)))
 && !function_exists('sm_validate_security_token'))
{
function sm_validate_security_token($token, $validity_period=0, $show_error=FALSE)
{

   global $data_dir, $username, $max_token_age_days,
          $disable_security_tokens;

   // bypass token validation?  CAREFUL!
   //
   if ($disable_security_tokens) return TRUE;

   // don't purge old tokens here because we already
   // do it when generating tokens
   //
   $tokens = sm_get_user_security_tokens(FALSE);

   // token not found?
   //
   if (empty($tokens[$token]))
   {
      if (!$show_error) return FALSE;
      logout_error(_("This page request could not be verified and appears to have expired."));
      exit;
   }

   $now = time();
   $timestamp = $tokens[$token];

   // whether valid or not, we want to remove it from
   // user prefs if it's old enough
   //
   if ($timestamp < $now - $validity_period)
   {
      unset($tokens[$token]);
      setPref($data_dir, $username, 'security_tokens', serialize($tokens));
   }

   // reject tokens that are too old
   //
   if (empty($max_token_age_days)) $max_token_age_days = 30;
   $old_token_date = $now - ($max_token_age_days * 86400);
   if ($timestamp < $old_token_date)
   {
      if (!$show_error) return FALSE;
      logout_error(_("The current page request appears to have originated from an untrusted source."));
      exit;
   }

   // token OK!
   //
   return TRUE;

}
}



