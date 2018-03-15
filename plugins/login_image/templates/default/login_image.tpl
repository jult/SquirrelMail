<?php

/**
  * login_image.tpl
  *
  * Template for the random login image inserted at the top
  * of the login page by the Random Login Image plugin.
  *
  * The following variables are available in this template:
  *
  * string  $random_login_image_src  The address of the image to use
  *                                  (for use in the "src" attribute
  *                                  of the image tag)
  * string  $random_login_image_height  The height to render the image 
  *                                     (for use in the "height" attribute
  *                                     of the image tag)
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


?><center>
    <img src="<?php echo $random_login_image_src . '"'
                       . (empty($random_login_image_height)
                          ? ''
                          : ' height="' . $random_login_image_height . '"')
                       . '></center>';

