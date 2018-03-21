<?php

/**
 * login.php -- simple login screen
 *
 * This a simple login screen. Some housekeeping is done to clean
 * cookies and find language.
 *
 * @copyright 1999-2018 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: login.php 1 2018-03-21 23:36:07Z jult $
 *
 */

/** This is the login page */
define('PAGE_NAME', 'login');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/i18n.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/constants.php');
require_once(SM_PATH . 'functions/page_header.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/forms.php');

/**
 * $squirrelmail_language is set by a cookie when the user selects
 * language and logs out
 */
set_up_language($squirrelmail_language, TRUE, TRUE);

/**
 * In case the last session was not terminated properly, make sure
 * we get a new one, but make sure we preserve session_expired_*
 */
$sep = '';
$sel = '';
sqGetGlobalVar('session_expired_post', $sep, SQ_SESSION);
sqGetGlobalVar('session_expired_location', $sel, SQ_SESSION);

/* blow away session */
sqsession_destroy();

/**
 * in some rare instances, the session seems to stick
 * around even after destroying it (!!), so if it does,
 * we'll manually flatten the $_SESSION data
 */
if (!empty($_SESSION)) {
    $_SESSION = array();
}

/**
 * Allow administrators to define custom session handlers
 * for SquirrelMail without needing to change anything in
 * php.ini (application-level).
 *
 * In config_local.php, admin needs to put:
 *
 *     $custom_session_handlers = array(
 *         'my_open_handler',
 *         'my_close_handler',
 *         'my_read_handler',
 *         'my_write_handler',
 *         'my_destroy_handler',
 *         'my_gc_handler',
 *     );
 *     session_module_name('user');
 *     session_set_save_handler(
 *         $custom_session_handlers[0],
 *         $custom_session_handlers[1],
 *         $custom_session_handlers[2],
 *         $custom_session_handlers[3],
 *         $custom_session_handlers[4],
 *         $custom_session_handlers[5]
 *     );
 * 
 * We need to replicate that code once here because PHP has
 * long had a bug that resets the session handler mechanism
 * when the session data is also destroyed.  Because of this
 * bug, even administrators who define custom session handlers
 * via a PHP pre-load defined in php.ini (auto_prepend_file)
 * will still need to define the $custom_session_handlers array 
 * in config_local.php.
 */
global $custom_session_handlers;
if (!empty($custom_session_handlers)) {
    $open    = $custom_session_handlers[0];
    $close   = $custom_session_handlers[1];
    $read    = $custom_session_handlers[2];
    $write   = $custom_session_handlers[3];
    $destroy = $custom_session_handlers[4];
    $gc      = $custom_session_handlers[5];
    session_module_name('user');
    session_set_save_handler($open, $close, $read, $write, $destroy, $gc);
}

/* put session_expired_* variables back in session */
sqsession_is_active();
if (!empty($sel)) {
    sqsession_register($sel, 'session_expired_location');
    if (!empty($sep)) 
        sqsession_register($sep, 'session_expired_post');
}

// Disable Browser Caching
//
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: Sat, 1 Jan 2000 00:00:00 GMT');

do_hook('login_cookie');

$loginname_value = (sqGetGlobalVar('loginname', $loginname) ? sm_encode_html_special_chars($loginname) : '');

/* Output the javascript onload function. */

$header = "<script language=\"JavaScript\" type=\"text/javascript\">\n" .
          "<!--\n".
          "  var alreadyFocused = false;\n".
          "  function squirrelmail_loginpage_onload() {\n".
          "    document.login_form.js_autodetect_results.value = '" . SMPREF_JS_ON . "';\n".
          "    if (alreadyFocused) return;\n".
          "    var textElements = 0;\n".
          "    for (i = 0; i < document.login_form.elements.length; i++) {\n".
          "      if (document.login_form.elements[i].type == \"text\" || document.login_form.elements[i].type == \"password\") {\n".
          "        textElements++;\n".
          "        if (textElements == " . (isset($loginname) ? 2 : 1) . ") {\n".
          "          document.login_form.elements[i].focus();\n".
          "          break;\n".
          "        }\n".
          "      }\n".
          "    }\n".
          "  }\n".
          "// -->\n".
          "</script>\n".
  "<style type=\"text/css\">\n".
        "body{\n".
        "margin: 0;".
        "padding: 0;".
        "background-image: url(\"/eekhoorn.jpg\");\n".
        "background-position: center center;\n".
        "background-repeat: no-repeat;\n".
        "background-size: 100% 100%;\n".
        "width:100vw;\n".
        "height: 100vh;\n".
        "}\n".
     "#content {position:relative;z-index:1;}".
  "</style>\n";

$custom_css = 'none';

// Load default theme if possible
// xxx if (@file_exists($theme[$theme_default]['PATH']))
//   @include ($theme[$theme_default]['PATH']);

if (! isset($color) || ! is_array($color)) {
    // Add default color theme, if theme loading fails
    $color = array();
    $color[0]  = '';  /*    TitleBar               */
    $color[1]  = '#990000';  /*                                   */
    $color[2]  = '#aa2200';  /*      Warning/Error Messages */
    $color[4]  = '#ababaa';  /* greyish         Normal Background      */
    $color[7]  = '#00aa00';  /* green          Links                  */
    $color[8]  = '#000000';  /* black         Normal text            */
}

// if any plugin returns TRUE here, the standard page header will be skipped
if (!boolean_hook_function('login_before_page_header', array($header), 1))
    displayHtmlHeader( "$org_name - " . _("Login"), $header, FALSE );

// note the div content part xxx
echo "<body text=\"$color[8]\" bgcolor=\"$color[4]\" link=\"$color[7]\" vlink=\"$color[7]\" alink=\"$color[7]\" onLoad=\"squirrelmail_loginpage_onload();\"><br /><div id=\"content\">" .
     "\n" . addForm('redirect.php', 'post', 'login_form');

$username_form_name = 'login_username';
$password_form_name = 'secretkey';
do_hook('login_top');


if(sqgetGlobalVar('mailtodata', $mailtodata)) {
    $mailtofield = addHidden('mailtodata', $mailtodata);
} else {
    $mailtofield = '';
}

/* If they don't have a logo, don't bother.. */
if (isset($org_logo) && $org_logo) {
    /* Display width and height like good little people */
    $width_and_height = '';
    if (isset($org_logo_width) && is_numeric($org_logo_width) &&
     $org_logo_width>0) {
        $width_and_height = " width=\"$org_logo_width\"";
    }
    if (isset($org_logo_height) && is_numeric($org_logo_height) &&
     $org_logo_height>0) {
        $width_and_height .= " height=\"$org_logo_height\"";
    }
}

echo html_tag( 'table',
    html_tag( 'tr',
        html_tag( 'td',
            '<center>'.
            ( isset($org_logo) && $org_logo
              ? '<img src="' . $org_logo . '" alt="' .
                sprintf(_("%s Logo"), $org_name) .'"' . $width_and_height .
                ' /><br />' . "\n"
              : '' ).
            ( (isset($hide_sm_attributions) && $hide_sm_attributions) ? '' :
            '<small>' . sprintf (_("version %s"), $version) . "\n".
            '  ' . _(".") . '<br /></small>' . "\n" ) .
            html_tag( 'table',
                html_tag( 'tr',
                    html_tag( 'td',
                        '<b>' . sprintf (_("%s"), $org_name) . "</b>\n",
                    'center', $color[0] )
                ) .
                html_tag( 'tr',
                    html_tag( 'td',  "\n" .
                        html_tag( 'table',
                            html_tag( 'tr',
                                html_tag( 'td',
                                    _("Name:") ,
                                'right', '', 'width="30%" id="username_td"' ) .
                                html_tag( 'td',
				    addInput($username_form_name, $loginname_value, 0, 0, ' onfocus="alreadyFocused=true;"'),
                                'left', '', 'width="70%"' )
                                ) . "\n" .
                            html_tag( 'tr',
                                html_tag( 'td',
                                    _("Password:") ,
                                'right', '', 'width="30%" id="secretkey_td"' ) .
                                html_tag( 'td',
				    addPwField($password_form_name, null, ' onfocus="alreadyFocused=true;"').
				    addHidden('js_autodetect_results', SMPREF_JS_OFF).
                    $mailtofield . 
				    addHidden('just_logged_in', '1'),
                                'left', '', 'width="70%"' )
                            ) ,
                        'center', $color[4], 'border="0" width="100%" id="login_table"' ) ,
                    'left',$color[4] )
                ) . 
                html_tag( 'tr',
                    html_tag( 'td',
                        '<center>'. addSubmit(_("Login"), 'smsubmit') .'</center>',
                    'left' )
                ),
            '', $color[4], 'border="0" width="350"' ) . '</center>',
        'center' )
    ) ,
'', $color[4], 'border="0" cellspacing="0" cellpadding="0" width="100%"' );
do_hook('login_form');
// note the added div closing xxx:
echo '</form></div>' . "\n";

do_hook('login_bottom');
?>
</body></html>
