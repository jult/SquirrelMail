<?php

/**
 * index.php
 *
 * This file simply takes any attempt to view source files and sends those
 * people to the login screen. At this point no attempt is made to see if the
 * person is logged in or not.
 *
 * @copyright (c) 1999-2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: index.php,v 1.1.2.2 2005/09/11 22:31:38 jervfors Exp $
 * @package squirrelmail
 * @subpackage encode
 */

header('Location: ../index.php');

