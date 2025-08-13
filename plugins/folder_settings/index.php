<?php
/**
 * index.php
 *
 * This file simply takes any attempt to view source files and sends those
 * people to the login screen. At this point no attempt is made to see if the
 * person is logged in or not.
 *
 * @copyright &copy; 1999-2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: index.php,v 1.1 2005/09/21 11:33:53 tokul Exp $
 * @package plugins
 * @subpackage folder_settings
 */
header('Location: ../index.php');

?>