<?php

/**
 * index.php
 *
 * This file simply takes any attempt to view source files and sends those
 * people to the login screen. At this point no attempt is made to see if the
 * person is logged in or not.
 *
 * @copyright 1999-2018 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: index.php 14749 2018-01-16 23:36:07Z pdontthink $
 * @package plugins
 * @subpackage delete_move_next
 */

header('Location: ../index.php');

?>