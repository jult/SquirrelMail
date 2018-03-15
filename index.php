<?php

/**
 * index.php
 *
 * Redirects to the login page.
 *
 * @copyright 1999-2018 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: index.php 14749 2018-01-16 23:36:07Z pdontthink $
 * @package squirrelmail
 */

// Are we configured yet?
if( ! file_exists ( 'config/config.php' ) ) {
    echo '<html><body><p><strong>ERROR:</strong> Config file ' .
        '&quot;<tt>config/config.php</tt>&quot; not found. You need to ' .
        'configure SquirrelMail before you can use it.</p></body></html>';
    exit;
}

// If we are, go ahead to the login page.
header('Location: src/login.php');

?>