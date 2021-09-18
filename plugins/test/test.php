<?php

/**
 * SquirrelMail Test Plugin
 * @copyright 2006-2021 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: test.php 14885 2021-02-05 19:19:32Z pdontthink $
 * @package plugins
 * @subpackage test
 */


define('SM_PATH', '../../');
include_once(SM_PATH . 'include/validate.php');

global $color;
displayPageHeader($color, 'none');

?>

<strong>Tests:</strong>
<br />
<br />
<p><a href="decodeheader.php">decodeHeader() test</a></p>
<p><a href="ngettext.php">ngettext() test</a></p>

</body>
</html>

