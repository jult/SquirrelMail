/**
  * common.js
  *
  * This file contains a set of general-purpose library
  * functions.
  *
  * @copyright &copy; 1999-2019 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id: common.js 14800 2019-01-08 04:27:15Z pdontthink $
  * @package squirrelmail
  * @since 1.4.23
  *
  */
//FIXME: we could provide a minimized version of this (currently this is 2k)



/**
  * Get page element by ID
  *
  * @param string id The id attribute of the desired page element
  *
  * @return object The requested page element or null if not found
  *
  * @since 1.4.23
  *
  */
function sm_get_element_by_id(id)
{
   if (document.getElementById)
      return document.getElementById(id);
   if (document.all)
      return document.all[id];
   if (document.layers)
      return document.layers[id];
   return null;
}



/**
  * Trim whitespace from beginning and end of string
  *
  * Doesn't use regular expressions for maximum
  * browser compatibility, although in 2013, this
  * is less and less of a concern...
  *
  * @param string string_to_trim The string to operate upon
  *
  * @return string The trimmed string
  *
  * @since 1.4.23
  *
  */
function sm_trim(string_to_trim)
{

   if (string_to_trim == null) return null;


   // we could probably use the following, but the code below works
   // works with even older browsers (pre-version-4 generation)
   //
   //return string_to_trim.replace(/^\s+|\s+$/g,"");


   while (string_to_trim.charAt(0) == ' '
       || string_to_trim.charAt(0) == '\\n'
       || string_to_trim.charAt(0) == '\\t'
       || string_to_trim.charAt(0) == '\\f'
       || string_to_trim.charAt(0) == '\\r')
      string_to_trim = string_to_trim.substring(1, string_to_trim.length);

   while (string_to_trim.charAt(string_to_trim.length - 1) == ' '
       || string_to_trim.charAt(string_to_trim.length - 1) == '\\n'
       || string_to_trim.charAt(string_to_trim.length - 1) == '\\t'
       || string_to_trim.charAt(string_to_trim.length - 1) == '\\f'
       || string_to_trim.charAt(string_to_trim.length - 1) == '\\r')
      string_to_trim = string_to_trim.substring(0, string_to_trim.length - 1);

   return string_to_trim;

}



