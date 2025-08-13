<?php

/**
  * SquirrelMail Test Plugin
  * @copyright 2006-2021 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id: functions.php 14885 2021-02-05 19:19:32Z pdontthink $
  * @package plugins
  * @subpackage test
  */

/**
  * Add link to menu at top of content pane
  *
  * @return void
  *
  */
function test_menuline_do() {

    displayInternalLink('plugins/test/test.php', 'Test', 'right');
    echo "&nbsp;&nbsp;\n";

}


