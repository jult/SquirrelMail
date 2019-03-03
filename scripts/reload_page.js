/**
  * reload_page.js
  *
  * This file contains functionality that reloads the
  * current page using an asynchronous HTTP request
  * to first determine if the server is responding;
  * if it isn't, the reload is attempted again later.
  *
  * Requires remote_request.js
  *
  * NOTE!  The script that calls for this code to be
  *        included should initialize the following
  *        variables:
  *
  * integer reload_interval The number of seconds between
  *                         page reloads
  * boolean use_advanced_page_reload Use safer but less
  *                                  backward compatible
  *                                  method of page refresh
  * string base_uri The SquirrelMail base_uri
  *
  * @copyright &copy; 1999-2019 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id: reload_page.js 14800 2019-01-08 04:27:15Z pdontthink $
  * @package squirrelmail
  * @since 1.4.23
  *
  */
//FIXME: we could provide a minimized version of this (currently this is 3.5k)



// The amount of time we are willing to wait for a response to
// having asked if the server is responding (in seconds)
//
var reload_request_timeout = 20;
// FIXME: Should this be configurable?
// FIXME: Also, we could argue that if use_advanced_page_reload is TRUE, this could be longer



// Set defaults if not given by caller
//
// (The script that calls for this code to be included should initialize
// these values as well as the use_advanced_page_reload (boolean) variable)
//
// The location of base_uri is going to be one directory up for
// most pages, so we use that as our default, but it won't be so
// for some pages, so it's best to initialize it on the PHP side
// using SquirrelMail's internal $base_uri
//
if (typeof(base_uri) == "undefined") base_uri = "../";
if (typeof(reload_interval) == "undefined") reload_interval = 600; // 10 minutes



/**
  * Attempts to reload the page after the given number of seconds
  * 
  * @param int interval_seconds How long to wait between update requests
  *
  */
function reload_later(interval_seconds)
{
   setTimeout(check_server_and_reload, (interval_seconds * 1000));
}



/**
 * Attempts to load a tiny image file from the server to see if it
 * responds; if so, a callback is provided that reloads the page
 *
 */
function check_server_and_reload()
{
   if (use_advanced_page_reload)
      sm_send_request("GET", window.location.href, "", "replace_document", false,
                      reload_request_timeout, 1000, "<!-- logout_error -->",
                      "server_or_connect_error", false, "", false);
   else
      // images/spacer.png is exactly 68 bytes in size
      sm_send_request("GET", base_uri + "images/spacer.png", "", "reload_now", false,
                      reload_request_timeout, 68, "", "server_or_connect_error",
                      false, "", true);
}



/**
  * In the case of errors, just reload the page later
  * 
  */
function server_or_connect_error()
{
// FIXME: We could display something on screen at this point to let the user know we had a problem completing a reload
   reload_later(reload_interval)
}



/**
  * Replaces the current page/document
  * 
  * NB: May not work on older versions of some browsers (IE)
  * 
  * @param string new_document_source The new document source code
  * 
  */
function replace_document(new_document_source)
{
   document.open();
   document.write(new_document_source);
   document.close();
}



/**
  * Reloads the page immediately
  * 
  */
function reload_now()
{
   window.location.reload(false);
}



// initiate the reload cycle
//
reload_later(reload_interval);



