<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/


    global $optpage_blocks;

    if (!defined('SM_PATH')) define('SM_PATH','../../../');

      // Set domain
      bindtextdomain ('archive_mail', SM_PATH . 'locale');
      textdomain ('archive_mail');

    $optpage_blocks[] = array (
        'name' => _("Archive Settings"),
        'url'  => '../plugins/archive_mail/includes/display_inside.php',
        'desc' => _("These settings allow you to modify the way you archive messages."),
        'js'   => false
    );

      // Unset domain
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
?>