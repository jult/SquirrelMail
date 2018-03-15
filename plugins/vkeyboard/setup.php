<?php
/*
    Squirrelmail plugin setup file.
    Copyright 2003-2008 Daniel K. Imori

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

if(!defined('SM_PATH')) {
   define('SM_PATH', '../../');
}

function squirrelmail_plugin_init_vkeyboard()
{
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks['login_form']['vkeyboard'] =
    'plugin_vkeyboard_main';
}

function plugin_vkeyboard_main()
{
    include_once(SM_PATH . 'plugins/vkeyboard/functions.php');

    plugin_vkeyboard_create_link();
}

function vkeyboard_version()
{
    return '0.9.1';
}

?>
