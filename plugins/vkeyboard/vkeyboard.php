<?php
/*
    vkeyboard main window.
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

// Defining constants

if(!defined('SM_PATH'))
   define('SM_PATH', '../../');

define('VK_PATH', SM_PATH . 'plugins/vkeyboard/');

// Including functions and settings

include_once(SM_PATH . 'functions/i18n.php');
include_once(SM_PATH . 'plugins/vkeyboard/config.php');
include_once(VK_PATH . 'layouts/' . $vkeyboard_layout . '.php');

// Checking global variables

$passformname = $_GET['passformname'];

// Changing the language to Squirrelmail settings

set_up_language($squirrelmail_language, TRUE, TRUE);  // uncertain

bindtextdomain('vkeyboard', VK_PATH . 'locale');
textdomain('vkeyboard');

// Creating initial HTML with JavaScript functions

echo "<html>\n" .
     "  <head>\n" .
     '    <title>';
echo _("Virtual Keyboard");
echo "</title>\n";
echo <<<END
    <script language="JavaScript">
    <!--
    var timer = $vkeyboard_timer;
    var timeouts = new Array($vkeyboard_timer);

    function add_key(key)
    {
       window.opener.document.forms[0].$passformname.value += key;
    }

    function vkeyboard_login()
    {
       window.opener.document.forms[0].submit();
       self.close();
    }

    function vkeyboard_clear()
    {
       window.opener.document.forms[0].$passformname.value = '';
    }

    function vkeyboard_close()
    {
       self.close();
    }

    function decreasecounter()
    {
       document.keyboard.counter.value--;
    }

    function startcounter()
    {
       document.keyboard.counter.value = timer;
       for(i = 1; i <= timer; i++) {
          timeouts[i] = setTimeout('decreasecounter()', i * 1000);
       }
    }

    function stopcounter()
    {
       document.keyboard.counter.value = 0;
       for(i = 1; i <= timer; i++) {
          clearTimeout(timeouts[i]);
       }
    }
    // -->
    </script>
END;
echo "  </head>\n" .
     "  <body>\n" .
     '    <p><table width="100%"><tr><td><b>';
echo _("Enter your password:");
echo '</b></td><td align="right"><a href="#" onclick="window.open(\'' . VK_PATH .
     'help.php\', \'\', \'toolbar=no,width=450,height=200,scrollbars=yes\')">';
echo _("Help");
echo "</td></tr></table></p>\n";
echo "    <form name=\"keyboard\">\n" .
     '      <p>';
echo _("The key will be pressed in ");
echo '<input type="text" name="counter" size="2" style="text-align: center; border-style: none">';
echo _(" seconds.");
echo "</p>\n";

// Creating the keys

if($vkeyboard_rand == 'yes') {
   srand((float) microtime() * 10000000);
   $rand_keys = array_rand($layout, count($layout));  // randomize the keys
   for($i = 0; $i < count($layout); $i++) {
      if($layout[$rand_keys[$i]] == '"')
         $value = 'value=\'"\'';  // " sucks
      else
         $value = 'value="' . $layout[$rand_keys[$i]] . '"';
      echo '<input style="width: ' . $vkeyboard_keysize . '; border-style: none" ' .
           'type="button" name="button' . $i . '" ' . $value .
           ' onclick="window.opener.document.forms[0].' . $passformname . '.value ' .
           '+= document.keyboard.button' . $i . '.value; document.keyboard.login.focus()"' .
           ' onmouseover="key=setTimeout(\'add_key(\\\'' . 
           ( ($layout[$rand_keys[$i]] == "'" || $layout[$rand_keys[$i]] == '\\') ? '\\\\\\' . $layout[$rand_keys[$i]] : $layout[$rand_keys[$i]] ).
           '\\\')\', timer * 1000); startcounter()"' .
           ' onmouseout="clearTimeout(key); stopcounter()">'. "\n";
   }
}
else {
   for($i = 0; $i < count($layout); $i++) {
      if($layout[$i] == '"')
         $value = 'value=\'"\'';  // " sucks
      else
         $value = 'value="' . $layout[$i] . '"';
      echo '<input style="width: ' . $vkeyboard_keysize . '; border-style: none" ' .
           'type="button" name="button' . $i . '" ' . $value .
           ' onclick="window.opener.document.forms[0].' . $passformname . '.value ' .
           '+= document.keyboard.button' . $i . '.value; document.keyboard.login.focus()"' .
           ' onmouseover="key=setTimeout(\'add_key(\\\'' .
           ( ($layout[$i] == "'" || $layout[$i] == '\\') ? '\\\\\\' . $layout[$i] : $layout[$i] ).
           '\\\')\', timer * 1000); startcounter()"' .
           ' onmouseout="clearTimeout(key); stopcounter()">';
   }
}

// Creating the buttons

echo '<p><input type="button" name="login" value="';
echo _("Login");
echo '" onclick="vkeyboard_login()"' .
     ' onmouseover="key=setTimeout(\'vkeyboard_login()\', timer * 1000); startcounter()"' .
     ' onmouseout="clearTimeout(key); stopcounter()">' .
     '&nbsp;&nbsp;<input type="button" value="';
echo _("Clear");
echo '" onclick="vkeyboard_clear()"' .
     ' onmouseover="key=setTimeout(\'vkeyboard_clear()\', timer * 1000); startcounter()"' .
     ' onmouseout="clearTimeout(key); stopcounter()">' .
     '&nbsp;<input type="button" value="';
echo _("Close");
echo '" onclick="vkeyboard_close()"' .
     ' onmouseover="key=setTimeout(\'vkeyboard_close()\', timer * 1000); startcounter()"' .
     ' onmouseout="clearTimeout(key); stopcounter()"></p>';

// Creating the final HTML
echo <<<END
    </form>
  </body>
</html>
END;

// Changing gettext domain to Squirrelmail

bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');

?>
