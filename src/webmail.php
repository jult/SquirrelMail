<?php

/**
 * webmail.php -- Displays the main frameset
 *
 * This file generates the main frameset. The files that are
 * shown can be given as parameters. If the user is not logged in
 * this file will verify username and password.
 *
 * @copyright 1999-2019 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: webmail.php 14800 2019-01-08 04:27:15Z pdontthink $
 * @package squirrelmail
 */

/** This is the webmail page */
define('PAGE_NAME', 'webmail');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/imap.php');

sqgetGlobalVar('username', $username, SQ_SESSION);
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);
sqgetGlobalVar('right_frame', $right_frame, SQ_GET);
if (sqgetGlobalVar('sort', $sort)) {
    $sort = (int) $sort;
}

if (sqgetGlobalVar('startMessage', $startMessage)) {
    $startMessage = (int) $startMessage;
}

if (!sqgetGlobalVar('mailbox', $mailbox)) {
    $mailbox = 'INBOX';
}

if(sqgetGlobalVar('mailtodata', $mailtodata)) {
    $mailtourl = 'mailtodata='.urlencode($mailtodata);
} else {
    $mailtourl = '';
}

// this value may be changed by a plugin, but initialize
// it first to avoid register_globals headaches
//
$right_frame_url = '';
do_hook('webmail_top');

/**
 * We'll need this to later have a noframes version
 *
 * Check if the user has a language preference, but no cookie.
 * Send him a cookie with his language preference, if there is
 * such discrepancy.
 */
$my_language = getPref($data_dir, $username, 'language');
if ($my_language != $squirrelmail_language) {
    sqsetcookie('squirrelmail_language', $my_language, time()+2592000, $base_uri);
}

set_up_language($my_language);

// prevent clickjack attempts
// FIXME: should we use DENY instead?  We can also make this a configurable value, including giving the admin the option of removing this entirely in case they WANT to be framed by an external domain
header('X-Frame-Options: SAMEORIGIN');

global $browser_rendering_mode, $head_tag_extra;
$output = ($browser_rendering_mode === 'standards' || $browser_rendering_mode === 'almost'
       ? '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'
       : /* "quirks" */ '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">').
          "\n<html><head>\n"

          // For adding a favicon or anything else that should be inserted in *ALL* <head> for *ALL* documents,
          // define $head_tag_extra in config/config_local.php
          // The string "###SM BASEURI###" will be replaced with the base URI for this SquirrelMail installation.
          // When not defined, a default is provided that displays the default favicon.ico.
          // If you override this and still want to use the default favicon.ico, you'll have to include the following
          // following in your $head_tag_extra string:
          // $head_tag_extra = '<link rel="shortcut icon" href="###SM BASEURI###favicon.ico" />...<YOUR CONTENT HERE>...';
          //
          . (empty($head_tag_extra) ? '<link rel="shortcut icon" href="' . sqm_baseuri() . 'favicon.ico" />'
          : str_replace('###SM BASEURI###', sqm_baseuri(), $head_tag_extra))

          // prevent clickjack attempts using JavaScript for browsers that
          // don't support the X-Frame-Options header...
          // we check to see if we are *not* the top page, and if not, check
          // whether or not the top page is in the same domain as we are...
          // if not, log out immediately -- this is an attempt to do the same
          // thing that the X-Frame-Options does using JavaScript (never a good
          // idea to rely on JavaScript-based solutions, though)
          . '<script type="text/javascript" language="JavaScript">'
          . "\n<!--\n"
          . 'if (self != top) { try { if (document.domain != top.document.domain) {'
          . ' throw "Clickjacking security violation! Please log out immediately!"; /* this code should never execute - exception should already have been thrown since it\'s a security violation in this case to even try to access top.document.domain (but it\'s left here just to be extra safe) */ } } catch (e) { self.location = "'
          . sqm_baseuri() . 'src/signout.php"; top.location = "'
          . sqm_baseuri() . 'src/signout.php" } }'
          . "\n// -->\n</script>\n"

          . "<meta name=\"robots\" content=\"noindex,nofollow\">\n"
          . "<title>$org_title</title>\n"
          . "</head>";

$left_size = getPref($data_dir, $username, 'left_size');
$location_of_bar = getPref($data_dir, $username, 'location_of_bar');

if (isset($languages[$squirrelmail_language]['DIR']) &&
    strtolower($languages[$squirrelmail_language]['DIR']) == 'rtl') {
    $temp_location_of_bar = 'right';
} else {
    $temp_location_of_bar = 'left';
}

if ($location_of_bar == '') {
    $location_of_bar = $temp_location_of_bar;
}
$temp_location_of_bar = '';

if ($left_size == "") {
    if (isset($default_left_size)) {
         $left_size = $default_left_size;
    }
    else {
        $left_size = 200;
    }
}

if ($location_of_bar == 'right') {
    $output .= "<frameset cols=\"*, $left_size\" id=\"fs1\">\n";
}
else {
    $output .= "<frameset cols=\"$left_size, *\" id=\"fs1\">\n";
}

/*
 * There are three ways to call webmail.php
 * 1.  webmail.php
 *      - This just loads the default entry screen.
 * 2.  webmail.php?right_frame=right_main.php&sort=X&startMessage=X&mailbox=XXXX
 *      - This loads the frames starting at the given values.
 * 3.  webmail.php?right_frame=folders.php
 *      - Loads the frames with the Folder options in the right frame.
 *
 * This was done to create a pure HTML way of refreshing the folder list since
 * we would like to use as little Javascript as possible.
 *
 * The test for // should catch any attempt to include off-site webpages into
 * our frameset.
 *
 * Note that plugins are allowed to completely and freely override the URI
 * used for the "right" (content) frame, and they do so by modifying the 
 * global variable $right_frame_url.
 *
 */

if (empty($right_frame) || (strpos(urldecode($right_frame), '//') !== false)) {
    $right_frame = '';
}

if ( strpos($right_frame,'?') ) {
    $right_frame_file = substr($right_frame,0,strpos($right_frame,'?'));
} else {
    $right_frame_file = $right_frame;
}

if (empty($right_frame_url)) {
    switch($right_frame_file) {
        case 'right_main.php':
            $right_frame_url = "right_main.php?mailbox=".urlencode($mailbox)
                           . (!empty($sort)?"&amp;sort=$sort":'')
                           . (!empty($startMessage)?"&amp;startMessage=$startMessage":'');
            break;
        case 'options.php':
            $right_frame_url = 'options.php';
            break;
        case 'folders.php':
            $right_frame_url = 'folders.php';
            break;
        case 'compose.php':
            $right_frame_url = 'compose.php?' . $mailtourl;
            break;
        case '':
            $right_frame_url = 'right_main.php';
            break;
        default:
            $right_frame_url =  urlencode($right_frame);
            break;
    } 
} 

if ($location_of_bar == 'right') {
    $output .= "<frame src=\"$right_frame_url\" name=\"right\" frameborder=\"1\">\n" .
               "<frame src=\"left_main.php\" name=\"left\" frameborder=\"1\">\n";
}
else {
    $output .= "<frame src=\"left_main.php\" name=\"left\" frameborder=\"1\">\n".
               "<frame src=\"$right_frame_url\" name=\"right\" frameborder=\"1\">\n";
}
$ret = concat_hook_function('webmail_bottom', $output);
if($ret != '') {
    $output = $ret;
}
echo $output;
?>
</frameset>
</html>
