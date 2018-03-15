<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/


    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
    } else if (!empty($HTTP_SERVER_VARS['HTTP_USER_AGENT'])) {
        $HTTP_USER_AGENT = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
    }
    // 2. browser and version
    if (ereg('MSIE ([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) {
        define('USR_BROWSER_VER', $log_version[1]);
        define('USR_BROWSER_AGENT', 'IE');
    } else if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) {
        define('USR_BROWSER_VER', $log_version[2]);
        define('USR_BROWSER_AGENT', 'OPERA');
    } else if (ereg('OmniWeb/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) {
        define('USR_BROWSER_VER', $log_version[1]);
        define('USR_BROWSER_AGENT', 'OMNIWEB');
    } else if (ereg('Mozilla/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) {
        define('USR_BROWSER_VER', $log_version[1]);
        define('USR_BROWSER_AGENT', 'MOZILLA');
    } else if (ereg('Konqueror/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) {
        define('USR_BROWSER_VER', $log_version[1]);
        define('USR_BROWSER_AGENT', 'KONQUEROR');
    } else {
        define('USR_BROWSER_VER', 0);
        define('USR_BROWSER_AGENT', 'OTHER');
    }

?>