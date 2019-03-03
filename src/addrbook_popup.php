<?php

/**
 * addrbook_popup.php
 *
 * Frameset for the JavaScript version of the address book.
 *
 * @copyright 1999-2019 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: addrbook_popup.php 14800 2019-01-08 04:27:15Z pdontthink $
 * @package squirrelmail
 * @subpackage addressbook
 */

/** This is the addrbook_popup page */
define('PAGE_NAME', 'addrbook_popup');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/** SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/addressbook.php');
   
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">

<html>
    <head>
<?php
    // For adding a favicon or anything else that should be inserted in *ALL* <head> for *ALL* documents,
    // define $head_tag_extra in config/config_local.php
    // The string "###SM BASEURI###" will be replaced with the base URI for this SquirrelMail installation.
    // When not defined, a default is provided that displays the default favicon.ico.
    // If you override this and still want to use the default favicon.ico, you'll have to include the following
    // following in your $head_tag_extra string:
    // $head_tag_extra = '<link rel="shortcut icon" href="###SM BASEURI###favicon.ico" />...<YOUR CONTENT HERE>...';
    //
    global $head_tag_extra;
    echo (empty($head_tag_extra) ? '<link rel="shortcut icon" href="' . sqm_baseuri() . 'favicon.ico" />'
       : str_replace('###SM BASEURI###', sqm_baseuri(), $head_tag_extra));
?>
        <meta name="robots" content="noindex,nofollow">
        <title><?php echo "$org_title: " . _("Address Book"); ?></title>
    </head>
    <frameset rows="60,*" border="0">
        <frame name="abookmain"
               marginwidth="0"
               scrolling="no"
               border="0"
               src="addrbook_search.php?show=form" />
        <frame name="abookres"
               marginwidth="0"
               border="0"
               src="addrbook_search.php?show=blank" />
    </frameset>
</html>
