<?php

/**
  * quote.tpl
  *
  * Template for the Quote of the Day inserted at the bottom
  * of the login page by the Quote of the Day at Login plugin.
  *
  * The following variables are available in this template:
  *
  * string  $quote  The quote text being inserted
  *
  *
  * @copyright &copy; 1999-2011 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package squirrelmail
  * @subpackage plugins
  */


// retrieve the template vars
//
extract($t);


?><br />
  <center>
  <img src="<?php echo sqm_baseuri(); ?>plugins/qotd_login/images/qotd.gif">
  <br />
  <br />
<?php echo $quote; ?>
  </center>

