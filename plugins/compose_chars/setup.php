<?php

function squirrelmail_plugin_init_compose_chars() {
	global $squirrelmail_plugin_hooks;
	$squirrelmail_plugin_hooks['compose_button_row']['compose_chars'] = 'char_popup';
}

function compose_chars_version() {
	return 0.1;
}

function char_popup() {
   //Add 'Characters button' link to compose buttonrow
//  if (!soupNazi()) {
    /**
     * Some people may choose to disable javascript even though their
     * browser is capable of using it. So these freaks don't complain,
     * use document.write() so the "Check Spelling" button is not
     * displayed if js is off in the browser.
     */
	echo '<input type="button" onclick="window.open(\'../plugins/compose_chars/cc_interface.php\', \'compose_char\',\'status=yes,width=370,height=220, resizable=yes\')" name="open_chars" value="Special Characters" />';
//  }
}

?>
