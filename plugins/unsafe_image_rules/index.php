<?php

/**
 * index.php
 *
 * This file simply takes any attempt to view source files and sends those
 * people to the login screen. At this point no attempt is made to see if the
 * person is logged in or not.
 *
 * @copyright &copy; 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: index.php,v 1.1 2006/01/26 22:28:26 jervfors Exp $
 * @package plugins
 * @subpackage unsafe_image_rules
 */

header('Location: ../index.php');

?>