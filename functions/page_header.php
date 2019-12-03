<?php

/**
 * page_header.php
 *
 * Prints the page header (duh)
 *
 * @copyright 1999-2019 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: page_header.php 14835 2019-11-26 18:51:38Z pdontthink $
 * @package squirrelmail
 */

/** Include required files from SM */
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/imap_mailbox.php');
require_once(SM_PATH . 'functions/global.php');

/* Always set up the language before calling these functions */
/**
 * @param string $title This is placed directly in the HTML <title> tag,
 *                      so should be sanitized and ready to go
 * @param boolean $xtra_param Any additional HTML code that should be
 *                            included in the <head> tag - this is also
 *                            sent to the browser as-is, so should be pre-
 *                            sanitized
 * @param boolean $do_hook When TRUE, the "generic_header" hook is fired
 *                         herein.
 * @param array $script_libs_param A list of strings which each point to
 *                                 a script to be added to the <head> of
 *                                 the page being built. Each string can
 *                                 be:
 *                                  - One of the pre-defined SM_SCRIPT_LIB_XXX
 *                                    constants (see functions/constants.php)
 *                                    that correspond to libraries that come
 *                                    with SquirrelMail
 *                                  - A path to a custom script (say, in a
 *                                    plugin directory) (detected by the
 *                                    existence of at least one path separator
 *                                    in the string) - the script is assumed
 *                                    to be and is included as JavaScript
 *                                  - A full tag ("<script>", "<style>" or
 *                                    other) that will be placed verbatim in
 *                                    the page header (detected by the presence
 *                                    of a "<" character at the beginning of
 *                                    the string). NOTE that $xtra provides the
 *                                    same function, without needing the string
 *                                    to start with "<"
 */
function displayHtmlHeader($title='SquirrelMail', $xtra_param='', $do_hook=TRUE, $script_libs_param=array()) {
    global $squirrelmail_language, $xtra, $script_libs;

    // $script_libs and $xtra are globalized to allow plugins to
    // modify them on the generic_header hook, but we also want to
    // respect the values passed in from the function args, thus:
    $xtra = $xtra_param;
    $script_libs = $script_libs_param;
    if (!is_array($script_libs))
        $script_libs = array($script_libs);

    if ( !sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION) ) {
        global $base_uri;
    }
    global $theme_css, $custom_css, $pageheader_sent, $browser_rendering_mode, $head_tag_extra;

    // prevent clickjack attempts
// FIXME: should we use DENY instead?  We can also make this a configurable value, including giving the admin the option of removing this entirely in case they WANT to be framed by an external domain
    header('X-Frame-Options: SAMEORIGIN');

    echo ($browser_rendering_mode === 'standards'
       ? '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'
       : ($browser_rendering_mode === 'almost'
         ? '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
         : /* "quirks" */ '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">')) .
         "\n\n" . html_tag( 'html' ,'' , '', '', '' ) . "\n<head>\n" .
         "<meta name=\"robots\" content=\"noindex,nofollow\">\n" .
         "<meta http-equiv=\"x-dns-prefetch-control\" content=\"off\">\n"

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
       . "\n// -->\n</script>\n";

    if ( !isset( $custom_css ) || $custom_css == 'none' ) {
        if ($theme_css != '') {
            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$theme_css\">";
        }
    } else {
        echo '<link rel="stylesheet" type="text/css" href="' .
             $base_uri . 'themes/css/'.$custom_css.'">';
    }

    if ($squirrelmail_language == 'ja_JP') {
        // Why is it added here? Header ('Content-Type:..) is used in i18n.php
        echo "<!-- \xfd\xfe -->\n";
        echo '<meta http-equiv="Content-type" content="text/html; charset=euc-jp">' . "\n";
    }

    if ($do_hook) {
        do_hook('generic_header');
    }

    echo "\n<title>$title</title>$xtra\n";

    // output <script> tags as needed (use array_unique so
    // more than one plugin can ask for the same library)
    // 
    // usage of $script_libs is discussed in the docs for this function above
    // 
    foreach (array_unique($script_libs) as $item) {
        if ($item{0} === '<')
            echo $item . "\n";
        else if (strpos($item, '/') !== FALSE || strpos($item, '\\') !== FALSE)
            echo '<script language="JavaScript" type="text/javascript" src="' . $item . '"></script>' . "\n";
        else
            echo '<script language="JavaScript" type="text/javascript" src="' . $base_uri . 'scripts/' . $item . '"></script>' . "\n";
    }

    /* work around IE6's scrollbar bug */
    echo <<<ECHO
<!--[if IE 6]>
<style type="text/css">
/* avoid stupid IE6 bug with frames and scrollbars */
body {
    width: expression(document.documentElement.clientWidth - 30);
}
</style>
<![endif]-->

ECHO;

    echo "\n</head>\n\n";

    /* this is used to check elsewhere whether we should call this function */
    $pageheader_sent = TRUE;
}

function makeInternalLink($path, $text, $target='') {
    sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION);
    if ($target != '') {
        $target = " target=\"$target\"";
    }
    return '<a href="'.$base_uri.$path.'"'.$target.'>'.$text.'</a>';
}

function displayInternalLink($path, $text, $target='') {
    echo makeInternalLink($path, $text, $target);
}

function displayPageHeader($color, $mailbox, $xtra='', $session=false) {

    global $hide_sm_attributions, $frame_top,
           $compose_new_win, $compose_width, $compose_height,
           $attachemessages, $provider_name, $provider_uri,
           $javascript_on, $default_use_mdn, $mdn_user_support,
           $startMessage, $org_title;

    sqgetGlobalVar('base_uri', $base_uri, SQ_SESSION );
    sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION );
    if (!isset($frame_top)) {
        $frame_top = '_top';
    }

    if ($session) {
        $compose_uri = $base_uri.'src/compose.php?mailbox='.urlencode($mailbox).'&amp;session='."$session";
    } else {
        $compose_uri = $base_uri.'src/compose.php?newmessage=1';
        $session = 0;
    }

    // only output JavaScript if actually turned on
    if($javascript_on || strpos($xtra, 'new_js_autodetect_results.value') ) {
        if ( !defined('PAGE_NAME') ) define('PAGE_NAME', NULL);
        switch ( PAGE_NAME ) {
        case 'read_body':
            $js ='';

            // compose in new window code
            if ($compose_new_win == '1') {
                if (!preg_match("/^[0-9]{3,4}$/", $compose_width)) {
                    $compose_width = '640';
                }
                if (!preg_match("/^[0-9]{3,4}$/", $compose_height)) {
                    $compose_height = '550';
                }
                $js .= "function comp_in_new(comp_uri) {\n".
                     "       if (!comp_uri) {\n".
                     '           comp_uri = "'.$compose_uri."\";\n".
                     '       }'. "\n".
                     '    var newwin = window.open(comp_uri' .
                     ', "_blank",'.
                     '"width='.$compose_width. ',height='.$compose_height.
                     ',scrollbars=yes,resizable=yes,status=yes");'."\n".
                     "}\n\n";
            }

            // javascript for sending read receipts
            if($default_use_mdn && $mdn_user_support) {
                $js .= "function sendMDN() {\n".
                         "    mdnuri=window.location+'&sendreceipt=1';\n" .
                         "    window.location = mdnuri;\n" .
                       "\n}\n\n";
            }

            // if any of the above passes, add the JS tags too.
            if($js) {
                $js = "\n".'<script language="JavaScript" type="text/javascript">' .
                      "\n<!--\n" . $js . "// -->\n</script>\n";
            }

            displayHtmlHeader($org_title, $js);
            $onload = $xtra;
          break;
        case 'compose':
            $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "var alreadyFocused = false;\n" .
             "function cursorToTop(element) {\n" .
             "    if (typeof element.selectionStart == 'number')\n" .
             // also works:
             // "        element.setSelectionRange(0, 0);\n" .
             "        element.selectionStart = element.selectionEnd = 0;\n" .
             "    else if (typeof element.createTextRange != 'undefined') {\n" .
             "        var selectionRange = element.createTextRange();\n" .
             // also works, but maybe more recent?:
             // "        selectionRange.collapse(true);\n" .
             "        selectionRange.moveStart('character', 0);\n" .
             "        selectionRange.select();\n" .
             "    }\n" .
             "}\n" .
             "function checkForm() {\n" .
             "\n    if (alreadyFocused) return;\n";

            global $action, $reply_focus;
            if (strpos($action, 'reply') !== FALSE && $reply_focus)
            {
                if ($reply_focus == 'select') $js .= "document.forms['compose'].body.select();}\n";
                else if ($reply_focus == 'focus') $js .= "document.forms['compose'].body.focus(); cursorToTop(document.forms['compose'].body);}\n";
                else if ($reply_focus == 'none') $js .= "}\n";
            }
            // no reply focus also applies to composing new messages
            else if ($reply_focus == 'none')
            {
                $js .= "}\n";
            }
            else
                $js .= "    var f = document.forms.length;\n".
                "    var i = 0;\n".
                "    var remembered_form = -1;\n".
                "    var pos = -1;\n".
                "    var remembered_pos = -1;\n".
                "    while( pos == -1 && i < f ) {\n".
                "        var e = document.forms[i].elements.length;\n".
                "        var j = 0;\n".
                "        while( pos == -1 && j < e ) {\n".
                "            if ( document.forms[i].elements[j].type == 'text' ) {\n".
                "                if ( document.forms[i].elements[j].id.substring(0, 13) == '__lastfocus__' ) {\n".
                "                    remembered_pos = j;\n".
                "                    remembered_form = i;\n".
                "                } else if ( document.forms[i].elements[j].id.substring(0, 11) != '__nofocus__' ) {\n".
                "                    pos = j;\n".
                "                }\n".
                "            }\n".
                "            j++;\n".
                "        }\n".
                "        i++;\n".
                "    }\n".
                "    if( pos >= 0 ) {\n".
                "        document.forms[i-1].elements[pos].focus();\n".
                "    } else if ( remembered_pos >= 0 ) {\n".
                "        document.forms[remembered_form].elements[remembered_pos].focus();\n".
                "    }\n".
                "}\n";

            $js .= "// -->\n".
                 "</script>\n";
            $onload = 'onload="checkForm();"';
            displayHtmlHeader($org_title, $js);
            break;

        default:
            $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "var alreadyFocused = false;\n" .
             "function checkForm() {\n".
             "   if (alreadyFocused) return;\n".
             "   var f = document.forms.length;\n".
             "   var i = 0;\n".
             "   var remembered_form = -1;\n".
             "   var pos = -1;\n".
             "   var remembered_pos = -1;\n".
             "   while( pos == -1 && i < f ) {\n".
             "       var e = document.forms[i].elements.length;\n".
             "       var j = 0;\n".
             "       while( pos == -1 && j < e ) {\n".
             "           if ( document.forms[i].elements[j].type == 'text' " .
             "            || document.forms[i].elements[j].type == 'password' ) {\n".
             "               if ( document.forms[i].elements[j].id.substring(0, 13) == '__lastfocus__' ) {\n".
             "                   remembered_pos = j;\n".
             "                   remembered_form = i;\n".
             "               } else if ( document.forms[i].elements[j].id.substring(0, 11) != '__nofocus__' ) {\n".
             "                   pos = j;\n".
             "               }\n".
             "           }\n".
             "           j++;\n".
             "       }\n".
             "       i++;\n".
             "   }\n".
             "   if( pos >= 0 ) {\n".
             "       document.forms[i-1].elements[pos].focus();\n".
             "   } else if ( remembered_pos >= 0 ) {\n".
             "       document.forms[remembered_form].elements[remembered_pos].focus();\n".
             "   }\n".
             "   $xtra\n".
             "}\n";

            if ($compose_new_win == '1') {
                if (!preg_match("/^[0-9]{3,4}$/", $compose_width)) {
                    $compose_width = '640';
                }
                if (!preg_match("/^[0-9]{3,4}$/", $compose_height)) {
                    $compose_height = '550';
                }
                $js .= "function comp_in_new(comp_uri) {\n".
                     "       if (!comp_uri) {\n".
                     '           comp_uri = "'.$compose_uri."\";\n".
                     '       }'. "\n".
                     '    var newwin = window.open(comp_uri' .
                     ', "_blank",'.
                     '"width='.$compose_width. ',height='.$compose_height.
                     ',scrollbars=yes,resizable=yes,status=yes");'."\n".
                     "}\n\n";

            }
        $js .= "// -->\n". "</script>\n";


        $onload = 'onload="checkForm();"';
        displayHtmlHeader($org_title, $js);
      } // end switch module
    } else {
        // JavaScript off
        displayHtmlHeader($org_title);
        $onload = '';
    }

    echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\" $onload>\n\n";
    /** Here is the header and wrapping table **/
    $shortBoxName = sm_encode_html_special_chars(imap_utf7_decode_local(
                      readShortMailboxName($mailbox, $delimiter)));
    if ( $shortBoxName == 'INBOX' ) {
        $shortBoxName = _("INBOX");
    }
    echo "<a name=\"pagetop\"></a>\n"
        . html_tag( 'table', '', '', $color[4], 'border="0" width="100%" cellspacing="0" cellpadding="2"' ) ."\n"
        . html_tag( 'tr', '', '', $color[9] ) ."\n"
        . html_tag( 'td', '', 'left' ) ."\n";
    if ( $shortBoxName <> '' && strtolower( $shortBoxName ) <> 'none' ) {
        echo '         ' . _("Current Folder") . ": <b>$shortBoxName&nbsp;</b>\n";
    } else {
        echo '&nbsp;';
    }
    echo  "      </td>\n"
        . html_tag( 'td', '', 'right' ) ."<b>\n";
    displayInternalLink ('src/signout.php', _("Sign Out"), $frame_top);
    echo "</b></td>\n"
        . "   </tr>\n"
        . html_tag( 'tr', '', '', $color[4] ) ."\n"
        . ($hide_sm_attributions ? html_tag( 'td', '', 'left', '', 'colspan="2"' )
                                 : html_tag( 'td', '', 'left' ) )
        . "\n";
    $urlMailbox = urlencode($mailbox);
    $startMessage = (int)$startMessage;
    echo makeComposeLink('src/compose.php?mailbox='.$urlMailbox.'&amp;startMessage='.$startMessage);
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ('src/addressbook.php', _("Addresses"));
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ('src/folders.php', _("Folders"));
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ('src/options.php', _("Options"));
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ("src/search.php?mailbox=$urlMailbox&amp;what=", _("Search"));
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ('src/help.php', _("Help"));
    echo "&nbsp;&nbsp;\n";

    do_hook('menuline');

    echo "      </td>\n";

    if (!$hide_sm_attributions)
    {
        echo html_tag( 'td', '', 'right' ) ."\n";
        if (!isset($provider_uri)) $provider_uri= 'http://squirrelmail.org/';
        if (!isset($provider_name)) $provider_name= 'SquirrelMail';
        echo '<a href="'.$provider_uri.'" target="_blank">'.$provider_name.'</a>';
        echo "</td>\n";
    }
    echo "   </tr>\n".
        "</table><br>\n\n";
}

/* blatently copied/truncated/modified from the above function */
function compose_Header($color, $mailbox) {

    global $delimiter, $hide_sm_attributions, $base_uri,
           $data_dir, $username, $frame_top, $compose_new_win;


    if (!isset($frame_top)) {
        $frame_top = '_top';
    }

    /*
        Locate the first displayable form element
    */
    if ( !defined('PAGE_NAME') ) define('PAGE_NAME', NULL);
    switch ( PAGE_NAME ) {
    case 'search':
        $pos = getPref($data_dir, $username, 'search_pos', 0 ) - 1;
        $onload = "onload=\"document.forms[$pos].elements[2].focus();\"";
        displayHtmlHeader (_("Compose"));
        break;
    default:
        $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "var alreadyFocused = false;\n" .
             "function checkForm() {\n" .
             "\n    if (alreadyFocused) return;\n";

            global $action, $reply_focus;
            if (strpos($action, 'reply') !== FALSE && $reply_focus)
            {
                if ($reply_focus == 'select') $js .= "document.forms['compose'].body.select();}\n";
                else if ($reply_focus == 'focus') $js .= "document.forms['compose'].body.focus();}\n";
                else if ($reply_focus == 'none') $js .= "}\n";
            }
            // no reply focus also applies to composing new messages
            else if ($reply_focus == 'none')
            {
                $js .= "}\n";
            }
            else
                $js .= "var f = document.forms.length;\n".
                "var i = 0;\n".
                "var remembered_form = -1;\n".
                "var pos = -1;\n".
                "var remembered_pos = -1;\n".
                "while( pos == -1 && i < f ) {\n".
                    "var e = document.forms[i].elements.length;\n".
                    "var j = 0;\n".
                    "while( pos == -1 && j < e ) {\n".
                        "if ( document.forms[i].elements[j].type == 'text' ) {\n".
                            "if ( document.forms[i].elements[j].id.substring(0, 13) == '__lastfocus__' ) {\n".
                                "remembered_pos = j;\n".
                                "remembered_form = i;\n".
                            "} else if ( document.forms[i].elements[j].id.substring(0, 11) != '__nofocus__' ) {\n".
                                "pos = j;\n".
                            "}\n".
                        "}\n".
                        "j++;\n".
                    "}\n".
                "i++;\n".
                "}\n".
                "if( pos >= 0 ) {\n".
                    "document.forms[i-1].elements[pos].focus();\n".
                "} else if ( remembered_pos >= 0 ) {\n".
                    "document.forms[remembered_form].elements[remembered_pos].focus();\n".
                "}\n".
            "}\n";
        $js .= "// -->\n".
                 "</script>\n";
        $onload = 'onload="checkForm();"';
        displayHtmlHeader (_("Compose"), $js);
        break;

    }

    echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\" $onload>\n\n";
}

