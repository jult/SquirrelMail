<?PHP
/*
 * Notify SquirrelMail Plugin
 *
 * Provides a minimal new mail notification page that will restore from
 * minimized in Javascript supporting browsers.
 *
 * Unfortunately this plugin requires Javascript on the browser.
 *
 * By Richard Gee (richard.gee@pseudocode.co.uk)
 *
 * Version 1.3
 * Copyright 2002 Pseudocode Limited.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

 /* $Id$ */

define('SM_PATH','../../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/imap.php');

// global vars set by SM
$username = $_SESSION['username'];
$key = $_COOKIE['key'];

// display address from user specified personal information
$display = getPref($data_dir, $username, 'email_address', $username);

if ($display == '') {
  $display = $username;
}

// user defined option - period between checks in minutes
$refresh = getPref($data_dir, $username, 'notify_period', '5');

// user defined option - whether to play sound
$sound = getPref($data_dir, $username, 'notify_sound', 'Y');

if ($refresh < 1 || $refresh > 30) {
  $refresh = 5;
}

$refresh *= 60000;

// vars for page content
$script = '';
$msg = '';
$title = 'No email';

// Login to IMAP server and check for unread mail
$imap = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
$status = sqimap_status_messages($imap, 'INBOX');
$check = $status['UNSEEN'];
sqimap_logout($imap);

// Form message and output HTML
if ($check > 0) {
  $msg = '<P STYLE="FONT-SIZE:11pt;FONT-WEIGHT:BOLD;COLOR:BLUE">'
         . $check . ' new message';

  if ($check > 1) {
    $msg .= 's';
  }

  $msg .= "</P>\n";

  if ($sound == 'Y') {
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
      $msg .= "<BGSOUND SRC=notify.wav LOOP=1>\n";
    }
    else {
      $msg .= "<EMBED SRC=notify.wav HIDDEN=true>\n";
    }
  }

  $script = "window.focus()\n";
  $script .= "var txt = 'EMAIL'\n";
  $script .= "var now = (new Date()).valueOf()\n";
  $script .= "t()\n\n";
  $script .= "function t() {\n";
  $script .= "  top.document.title = txt\n";
  $script .= "  txt = (txt == 'EMAIL' ? 'email' : 'EMAIL')\n";
  $script .= "  setTimeout(t, 1000)\n\n";
  $script .= "  if ((new Date()).valueOf() - now > " . $refresh . ") {\n";
  $script .= "    location.reload()\n";
  $script .= "  }\n";
  $script .= "}\n";

  $title = 'EMAIL';
}
else {
  $msg = "<P STYLE=\"FONT-SIZE:11pt;FONT-WEIGHT:BOLD\">No new messages</P>\n";
  $script = "setTimeout(\"location.reload()\"," . $refresh . ")\n";
}

$focus = str_replace('notify.php', 'focus.php', $_SERVER['REQUEST_URI']);
?>
<HTML><HEAD><TITLE><?PHP echo $title ?></TITLE></HEAD><STYLE><!--
P {MARGIN-TOP:0px;MARGIN-BOTTOM:6px}
//--></STYLE><SCRIPT><!--
<?PHP echo $script ?>
//--></SCRIPT><BODY STYLE="FONT-FAMILY:Arial,sans-serif;FONT-SIZE:9pt;BACKGROUND:#FFFFFF"><CENTER><B><? echo $display; ?></B><BR><P STYLE="FONT-SIZE:8pt"><?PHP echo date('H:i:s'); ?></P><?PHP echo $msg ?><A HREF="<?=$focus?>" TARGET="squirrelmail">Go to SquirrelMail Inbox</A></BODY></HTML>