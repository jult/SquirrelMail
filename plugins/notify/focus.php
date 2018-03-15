<?PHP
/*
 * Notify SquirrelMail Plugin
 *
 * By Richard Gee (richard.gee@pseudocode.co.uk)
 *
 * Version 1.3
 * Copyright 2002 Pseudocode Limited.
 *
 */
$smpage = str_replace('plugins/notify/focus.php', 'src/webmail.php', $_SERVER['REQUEST_URI']);
?>
<HTML><SCRIPT><!--
window.focus()
document.location = '<?=$smpage?>'
</SCRIPT></HTML>