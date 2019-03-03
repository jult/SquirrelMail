/**
  * remote_request.js
  *
  * This file contains a set of library functions for
  * use in making remote requests to the "SquirrelMail
  * API" or other resources.
  *
  * At the time this was added to SquirrelMail 1.4.23-svn,
  * it came from an unfinished implementation in 1.5.2
  * that was intended to match the features made available
  * through the SquirrelMail RPC API (squirrelmail_rpc.php)
  * which is not part of version 1.4.x.  As such, this code
  * is mostly useful for miscellaneous/generic purposes in
  * the context of version 1.4.x.
  *
  * @copyright &copy; 1999-2019 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id: remote_request.js 14800 2019-01-08 04:27:15Z pdontthink $
  * @package squirrelmail
  * @since 1.4.23
  *
  * EXAMPLE: sm_send_request("POST", "src/squirrelmail_rpc.php", "delete_id=32323&startMessage=10", "parseStandardSquirrelMailResponse", true, 0, "", false, "", false);
  *
  */
//FIXME: we could provide a minimized version of this (currently this is 10.5k)



// debug levels:
//
//    0 = off
//    1 = basic error reporting only
//    2 = verbose...
//    3 = too verbose
//
var debug = 0;



// debug output target
//
//    0 = alert popup
//    1 = JavaScript console (browser must support console.log)
//
var debug_output_target = 0;



/**
  * Sends debug message to console or alert popup
  *
  * @param string  message  The debug output
  *
  * @since 1.4.23
  *
  */
function debug_print(message)
{
   if (debug_output_target == 1)
      console.log(message);
   else
      alert(message);
}



/**
  * Retrieves cross-browser compatible request object
  *
  * @since 1.4.23
  *
  */
function sm_get_xml_http_object()
{

   if (debug >= 3) debug_print("Attempting to get XMLHttp object...");
   var xml_http_object = null;

   // Internet Explorer, always the odd one out
   //
   if (window.ActiveXObject)
   {
      // instead of the line below, try to
      // use the newer MS version of XMLHttp...
      //
      // xml_http_object = new ActiveXObject("Microsoft.XMLHTTP");
      //
      try
      {
         xml_http_object = new ActiveXObject("Msxml2.XMLHTTP")
         if (debug >= 3) debug_print("Got IE XMLHTTP version 2");
      }
      catch(e)
      {
         try
         {
            xml_http_object = new ActiveXObject("Microsoft.XMLHTTP")
            if (debug >= 3) debug_print("Got IE XMLHTTP version 1");
         }
         catch(e) {}
      }
   }

   // Safari and Mozilla-like browsers
   //
   if (xml_http_object == null && window.XMLHttpRequest)
   {
      xml_http_object = new XMLHttpRequest();
      if (debug >= 3) debug_print("Got standard XMLHttpRequest");
      if (xml_http_object.overrideMimeType)
      {
         xml_http_object.overrideMimeType("text/xml");
      }
   }

   return xml_http_object;

}



/**
  * Send Server Request
  *
  * @param string  method  The desired request method (GET, POST, HEAD, etc)
  * @param string  uri  The target request URI
  * @param string  content  Request content; for a typical POST request, arguments
  *                         should be compiled into a GET-style query string
  * @param string  result_function  The name of the function that will know how to parse
  *                                 the result for this request - it is called upon completion
  *                                 of a successful request with a single parameter which
  *                                 is the request result (format of which is dictated by
  *                                 the "result_in_xml" parameter)
  * @param boolean result_in_xml  Indicates if the result function expects the request result
  *                               in XML format (TRUE) or just as a plain string (FALSE)
  * @param int  max_wait  If the request should time out (cancel itself) after a certain
  *                       time, this is the desired number of seconds (set to zero not to
  *                       use this functionality)
  * @param int  minimum_response_size  When set to anything greater than zero, the
  *                                    response size must be equal to or greater than this,
  *                                    otherwise an error will be triggered (code 599)
  *                                    (set to a negative number to skip response size test)
  * @param int  good_string  When not empty, if the response includes this string,
  *                          the response is always considered to be valid (all other
  *                          tests are skipped).
  * @param string  error_function  The name of the function that will be called if any
  *                                error occurs (beside timeout). It will be called with
  *                                two parameters: the error code and error message (note
  *                                that these can be empty in some cases). If given as an
  *                                empty string, errors are handled internally.
  * @param boolean show_server_side_error_alert Upon server side errors (5xx), should a
  *                                             pop-up error be displayed to the end user?
  * @param string  timeout_message  A message to be displayed to the user if the request
  *                                 times out per the "max_wait" parameter; set as an empty
  *                                 string if no message is needed
  * @param boolean make_query_unique  If TRUE, timestamp is added to the queryString (used
  *                                   to trick browser caching)
  *
  * @since 1.4.23
  *
  */
function sm_send_request(method, uri, content, result_function, result_in_xml,
                         max_wait, minimum_response_size, good_string, error_function,
                         show_server_side_error_alert, timeout_message,
                         make_query_unique)
{

   // grab request object, check if not supported
   //
   var xml_http_request = sm_get_xml_http_object();
   if (xml_http_request == null)
   {
      if (debug >= 1) debug_print("Sorry, your browser is not compatible with this page");
      return;
   }


   // miscellaneous prep...
   //
   var now = new Date();
   var timer_name = 'timer' + now.getTime();
   if (make_query_unique)
   {
      if (uri.indexOf("?") == -1)
         uri += "?";
      else
         uri += "&";
      uri += "timestamp=" + now.getTime();
   }


   // get request ready
   //
   xml_http_request.open(method, uri, true);
   xml_http_request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
   if (debug >= 3) debug_print("The request has been set up...");


   // set up response handling
   //
   xml_http_request.onreadystatechange = function()
   {

      // attempting to access status of request upon error throws exception...
      // (thus we need a try/catch)
      //
      try
      {

         if (debug >= 3) debug_print("Request state has changed... " + xml_http_request.readyState);
         if (xml_http_request.readyState == 4)
         {

            // turn off timeout timer if needed
            //
            if (max_wait > 0)
            {
               clearTimeout(eval(timer_name));
            }

            if (debug >= 2) debug_print("Request has finished.  Status: " + xml_http_request.status + " - " + xml_http_request.statusText);
            response_string = xml_http_request.responseText;
            response_xml = xml_http_request.responseXML;
            switch (xml_http_request.status)
            {

               // page-not-found error
               //
               case 404:
                  if (debug >= 1) debug_print("Error: Not Found. The requested URI " + uri + " could not be found.");
                  // NB: you can get single header by using xml_http_request.getResponseHeader('header name')
                  if (debug >= 3) debug_print("Response headers:\n\n" + xml_http_request.getAllResponseHeaders());
                  if (error_function != "")
                  {
                     if (debug >= 3) debug_print("Calling error handler: " + error_function);
                     eval(error_function + "(xml_http_request.status, xml_http_request.statusText);");
                  }
                  break;


               // handle server-side errors
               //
               case 500:
                  if (debug >= 1 || show_server_side_error_alert) debug_print("Error: Server Error " + xml_http_request.status + " - " + xml_http_request.statusText + " - " + response_string);
                  if (error_function != "")
                  {
                     if (debug >= 3) debug_print("Calling error handler: " + error_function);
                     eval(error_function + "(xml_http_request.status, xml_http_request.statusText);");
                  }
                  break;


               // request succeeded
               //
               case 200:

                  request_is_known_ok = false;
                  if (good_string != '' && response_string.indexOf(good_string) > -1)
                  {
                     request_is_known_ok = true;
                  }

// FIXME: do we want to do something else here for application-generated errors or skip that and deal with it when the caller evaluates the returned contents? (yeah, for now, that's fine)
//                  // alert custom error or debug messages
//                  //
//                  if (response_string.indexOf("Administration") > -1 || response_string.indexOf("Debug:") > -1)
//                  {
//                     if (debug >= 3) debug_print("Application error: " + response_string);
//                     //debug_print(response_string);
//                     if (error_function != "")
//                     {
//                        if (debug >= 3) debug_print("Calling error handler: " + error_function);
//                        eval(error_function + "(xml_http_request.status, xml_http_request.statusText);");
//                     }
//                  }
//
//                  // no errors occurred; call the target result function
//                  else
                  if (!request_is_known_ok && minimum_response_size > 0 && response_string.length < minimum_response_size)
                  {
                     if (debug >= 3) debug_print("Calling error handler: " + error_function);
                     eval(error_function + "(599, 'Response size too small');");
                  }
                  else
                  {
                     if (debug >= 3) debug_print("Request succeeded; calling request handler: " + result_function);
                     if (result_in_xml)
                        eval(result_function + "(response_xml);");
                     else
                        eval(result_function + "(response_string);");
                  }
                  break;


               // any other conditions...
               //
               default:
                  if (debug >= 1) debug_print("Error: " + xml_http_request.status + " - " + xml_http_request.statusText);
                  if (error_function != "")
                  {
                     if (debug >= 3) debug_print("Calling error handler: " + error_function);
                     eval(error_function + "(xml_http_request.status, xml_http_request.statusText);");
                  }
                  break;

            }
         }
      }
      catch(e)
      {
         // NB: Attention developers - if you give a non-existent callback, you'll land here
         if (debug >= 1) debug_print("Sorry, there was a problem accessing the server or invalid response callback: " + e.description);
         if (error_function != "")
         {
            if (debug >= 3) debug_print("Calling error handler: " + error_function);
            eval(error_function + "(0, e.description);");
         }
      }
   }

   if (debug >= 3) debug_print("Launching request...");
   xml_http_request.send(content);

   // turn on timeout timer if needed
   //
   if (max_wait > 0)
   {
      if (timeout_message != "")
         display_message = " debug_print(\"" + timeout_message.replace(/"/g, '\\"') + "\"); ";
      else
         display_message = "";

      eval("var " + timer_name
         + " = setTimeout(function() { xml_http_request.abort(); "
         + display_message + "}, " + (max_wait * 1000) + ");");
   }

}



