<?php

/**
 * SquirrelMail Preview Pane Plugin
 *
 * @copyright 1999-2017 The SquirrelMail Project Team
 * @author Paul Lesniewski <paul@squirrelmail.org>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: empty_frame.php 14643 2017-01-27 20:34:08Z pdontthink $
 * @package plugins
 * @subpackage preview_pane
 */


include_once('../../include/init.php');

global $org_title;
displayHtmlHeader($org_title, '', FALSE, FALSE);

$oTemplate->display('plugins/preview_pane/empty_frame.tpl');
$oTemplate->display('footer.tpl');

