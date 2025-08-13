<?php
/*
    vkeyboard functions.
    Copyright 2004 Daniel K. Imori

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

if(!defined('SM_PATH')) {
   define('SM_PATH', '../../');
}

define('VK_PATH', SM_PATH . 'plugins/vkeyboard/');

// Function to create the link on login's page

function plugin_vkeyboard_create_link()
{
    include_once(SM_PATH . 'plugins/vkeyboard/config.php');
    include_once(SM_PATH . 'functions/i18n.php');

    global $password_form_name;

    // Changing the language to Squirrelmail settings

    bindtextdomain('vkeyboard', VK_PATH . 'locale');
    textdomain('vkeyboard');

    // Displaying message or button

    if($vkeyboard_link == 'message') {
       echo '<p align="center" style="font-size: ' . $vkeyboard_msgsize . '">';
       echo _("Are you using a public computer?");
       echo '<br>';
       echo _("Enter your password with the ");
       echo '<a href="#" onclick="window.open(\'' . VK_PATH .
            'vkeyboard.php?passformname=' . $password_form_name .
            '\', \'\', \'toolbar=no,width=' . $vkeyboard_width .
            ',height=' . $vkeyboard_height . '\')">';
       echo _("virtual keyboard");
       echo '</a></p>';
    }
    else {
       echo '<p align="center"><input type="button" value="';
       echo _("Open Virtual Keyboard");
       echo '" onclick="window.open(\'' . VK_PATH .
            'vkeyboard.php?passformname=' . $password_form_name .
            '\', \'\', \'toolbar=no,width=' . $vkeyboard_width .
            ',height=' . $vkeyboard_height . '\')"></p>';
    }

    // Changing gettext domain to Squirrelmail

    bindtextdomain('squirrelmail', SM_PATH . 'locale');
    textdomain('squirrelmail');
}

?>
