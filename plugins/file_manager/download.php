<?php


function doDownload($filename, $fullPath, $absolute_dl)
{

   header('Pragma: ');
   // from /src/download.php:  
   header('Cache-Control: cache');

   // from php.net:
   //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');    // Date in the past
   //header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
   //header('Cache-Control: no-store, no-cache, must-revalidate');  // HTTP/1.1
   //header('Cache-Control: post-check=0, pre-check=0', false);
   //header('Pragma: no-cache');                          // HTTP/1.0



   list($type0, $type1) = explode('/', getMimeType($fullPath));
   if (empty($type0)) $type0 = "application";
   if (empty($type1)) $type1 = "octet-stream";


   DumpHeaders($type0, $type1, $filename, $fullPath, ($absolute_dl=='true'));


   if (!($FILE = fopen($fullPath, 'r')))
   {
      echo ''; echo '';
      echo '<html><body>';
      bindtextdomain('file_manager', '../plugins/file_manager/locale');
      textdomain('file_manager');
      echo '<h4>' . _("FILE DOES NOT EXIST OR PROBLEM OPENING FILE") . '</h4>';
      bindtextdomain('squirrelmail', '../locale');
      textdomain('squirrelmail');
      echo '</body></html>';
      exit(1);
   }


   //fpassthru($FILE);
   //while (!feof($FILE)) { $buffer = fread($FILE, 4096); print $buffer; }
   while (!feof($FILE)) echo fread($FILE, 4096);

   fclose($FILE);

   exit;


}



   //
   // from here on is a modified rip-off of /src/download.php...
   //


/*
 * This function is verified to work with Netscape and the *very latest*
 * version of IE.  I don't know if it works with Opera, but it should now.
 */
function DumpHeaders($type0, $type1, $filename, $fullPath, $force) {

    if (defined('SM_PATH'))
       include_once(SM_PATH . 'plugins/file_manager/functions.php');
    else
       include_once('../plugins/file_manager/functions.php');

    // get global variables for versions of PHP < 4.1
    //
    if (!compatibility_check_php_version(4, 1)) {
       global $HTTP_SERVER_VARS;
       $_SERVER = $HTTP_SERVER_VARS;
    }

    $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];

    $isIE = 0;

    if (strstr($HTTP_USER_AGENT, 'compatible; MSIE ') !== false &&
        strstr($HTTP_USER_AGENT, 'Opera') === false) {
        $isIE = 1;
    }

    if (strstr($HTTP_USER_AGENT, 'compatible; MSIE 6') !== false &&
        strstr($HTTP_USER_AGENT, 'Opera') === false) {
        $isIE6 = 1;
    }

    $filename = preg_replace('[^-a-zA-Z0-9\.]', '_', $filename);

    // A Pox on Microsoft and it's Office!
    if (! $force) {
        // Try to show in browser window
        header("Content-Disposition: inline; filename=\"$filename\"");
        header("Content-Type: $type0/$type1; name=\"$filename\"");
        header("Content-Length: " . (filesize($fullPath)));
        header("Content-transfer-encoding: binary"); 
    } else {
        // Try to pop up the "save as" box
        // IE makes this hard.  It pops up 2 save boxes, or none.
        // http://support.microsoft.com/support/kb/articles/Q238/5/88.ASP
        // But, accordint to Microsoft, it is "RFC compliant but doesn't
        // take into account some deviations that allowed within the
        // specification."  Doesn't that mean RFC non-compliant?
        // http://support.microsoft.com/support/kb/articles/Q258/4/52.ASP
        //
        // The best thing you can do for IE is to upgrade to the latest
        // version
        if ($isIE && !isset($isIE6)) {
            // http://support.microsoft.com/support/kb/articles/Q182/3/15.asp
            // Do not have quotes around filename, but that applied to
            // "attachment"... does it apply to inline too?
            //
            // This combination seems to work mostly.  IE 5.5 SP 1 has
            // known issues (see the Microsoft Knowledge Base)
            header("Content-Disposition: inline; filename=$filename");

            // This works for most types, but doesn't work with Word files
            header("Content-Type: application/download; name=\"$filename\"");

            header("Content-Length: " . (filesize($fullPath)));
            header("Content-transfer-encoding: binary"); 

            // These are spares, just in case.  :-)
            //header("Content-Type: $type0/$type1; name=\"$filename\"");
            //header("Content-Type: application/x-msdownload; name=\"$filename\"");
            //header("Content-Type: application/octet-stream; name=\"$filename\"");
        } else {
            header("Content-Disposition: attachment; filename=\"$filename\"");
            // application/octet-stream forces download for Netscape
            header("Content-Type: application/octet-stream; name=\"$filename\"");

            header("Content-Length: " . (filesize($fullPath)));
            header("Content-transfer-encoding: binary"); 
        }
    }
}



function getMimeType($filename)
{

   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/file_manager/mime_types.php');
   else
      include_once('../plugins/file_manager/mime_types.php');

   global $mimetypes;

   if (in_array(substr(strrchr(strtolower($filename), '.'), 1), array_keys($mimetypes)))
      return $mimetypes[substr(strrchr(strtolower($filename), '.'), 1)];
   else
      return '/';

}

?>
