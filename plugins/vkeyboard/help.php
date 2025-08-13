<?php
/*
    vkeyboard help window.
    Copyright 2003, 2004 Daniel K. Imori

    This file is part of vkeyboard plugin.

    vkeyboard plugin is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    vkeyboard plugin is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with vkeyboard plugin; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Defining constants

if(!defined('SM_PATH'))
   define('SM_PATH', '../../');

define('VK_PATH', SM_PATH . 'plugins/vkeyboard/');

// Including functions i18n

include_once(SM_PATH . 'functions/i18n.php');

// Changing the language to Squirrelmail settings

set_up_language($squirrelmail_language, TRUE, TRUE);  // uncertain

bindtextdomain('vkeyboard', VK_PATH . 'locale');
textdomain('vkeyboard');

// Generating the page

echo "<html>\n" .
     "  <head>\n" .
     "  <title>";
echo _("Virtual Keyboard: Help");
echo "</title>\n" .
     "  </head>\n" .
     "  <body>\n" .
     "    <p><b>";
echo _("What's a virtual keyboard?");
echo "</b></p>\n" .
     "    <p>";
echo _("It's a keyboard that simulates a real keyboard. ;)");
echo "<br>";
echo _("It protects your password against keyloggers, which are hidden programs that capture everything that you type or click.");
echo "<br>";
echo _("So, you can increase your security, using the virtual keyboard and its \"automatic click\" function.");
echo "<br>";
echo _("For this, you must point the mouse and keep it for few seconds on the key that you want to type.");
echo "</p>\n" .
     "  </body>\n" .
     "</html>\n";

// Changing gettext domain to Squirrelmail

bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');

?>
