<?php

/**
 * SquirrelMail Test Plugin
 *
 * This page tests the ngettext() function.
 *
 * @copyright 2006-2021 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: ngettext.php 14885 2021-02-05 19:19:32Z pdontthink $
 * @package plugins
 * @subpackage test
 */

define('SM_PATH', '../../');
include_once(SM_PATH . 'include/validate.php');

global $color;

displayPageHeader($color, 'none');

sq_change_text_domain('test');

?>
<strong>ngettext Test Strings:</strong>

<p>The results of this test depend on your current language (translation) selection (see Options==>Display Preferences) and the corresponding translation strings in locale/xx/LC_MESSAGES/test.mo</p>

<pre>

<?php

for ($i = -10 ; $i <= 250 ; $i++) {
    echo sprintf(ngettext("%s squirrel is on the tree.", "%s squirrels are on the tree.", $i), $i);
    echo "\n";
}

echo "</pre></body></html>";

sq_change_text_domain('squirrelmail');


