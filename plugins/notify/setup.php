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

// set hooks for link in left window and options
function squirrelmail_plugin_init_notify() {
  global $squirrelmail_plugin_hooks;
  $squirrelmail_plugin_hooks['left_main_after']['notify'] = 'notify_link';
  $squirrelmail_plugin_hooks['options_display_inside']['notify'] = 'notify_options';
  $squirrelmail_plugin_hooks['loading_prefs']['notify'] = 'load_notify_prefs';
  $squirrelmail_plugin_hooks['options_display_save']['notify'] = 'save_notify_prefs';
}

// add link in left window to open notification window
function notify_link() {
  echo '<BR><BR><CENTER><SMALL><A HREF="javascript:void(0)" ONCLICK="window.open(';
  echo '\'../plugins/notify/notify.php\',\'emailnotify\',\'width=250,height=80\')">';
  echo 'Show Notify Popup</A></SMALL></CENTER>' . "\n";
}

// load preferences
function load_notify_prefs() {
  global $username, $data_dir, $notify_period, $notify_sound;
  $notify_period = getPref($data_dir, $username, 'notify_period', '5');
  $notify_sound = getPref($data_dir, $username, 'notify_sound', 'Y');

  if ($notify_period < 1 || $notify_period > 30) {
    $notify_period = 5;
  }
}

// options (timeout period)
function notify_options() {
  global $notify_period, $notify_sound;
  echo '<TR><TD ALIGN=CENTER VALIGN=MIDDLE COLSPAN=2 NOWRAP><B>';
  echo 'Notify Popup Options</B></TD></TR>' . "\n";
  echo '<TR><TD ALIGN=RIGHT>New mail check period (minutes):</TD>';
  echo '<TD><INPUT NAME="notify_period" TYPE="TEXT" SIZE="4" VALUE="';
  echo $notify_period . '"></TD></TR>' . "\n";
  echo '<TR><TD ALIGN=RIGHT>Play sound:</TD>';
  echo '<TD><INPUT NAME="notify_sound" TYPE="CHECKBOX" VALUE=""';
  echo ($notify_sound == 'Y' ? ' CHECKED' : '') . '></TD></TR>' . "\n";
}

// save preferences
function save_notify_prefs() {
  global $username, $data_dir;

  if ((float)substr(PHP_VERSION, 0, 3) < 4.1 ) {
    global $_POST;
  }

  $notify_period = 5;

  if (isset($_POST['notify_period'])) {
    $notify_period = $_POST['notify_period'];
  }

  if ($notify_period < 1 || $notify_period > 30) {
    $notify_period = 5;
  }

  $notify_sound = 'N';

  if (isset($_POST['notify_sound'])) {
    $notify_sound = 'Y';
  }

  setPref($data_dir, $username, 'notify_period', $notify_period);
  setPref($data_dir, $username, 'notify_sound', $notify_sound);
}
