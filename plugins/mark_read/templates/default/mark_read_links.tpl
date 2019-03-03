<?php

/**
  * mark_read_links.tpl
  *
  * Template for showing the read/unread links next to a folder
  * name for the Mark Read plugin.
  *
  * The following variables are available in this template:
  *
  * string  $read_text         The (pre-translated) read link text string.
  * string  $read_title_text   The (pre-translated) read link title attribute
  *                            text string.
  * string  $mark_read_uri     The URI of the script that performs the
  *                            mark-all-as-read action (empty if the read
  *                            link is not to be displayed).
  * string  $read_onclick      The contents of the onclick handler for the
  *                            read link, which may be empty if not in use.
  * string  $unread_text       The (pre-translated) unread link text string.
  * string  $unread_title_text The (pre-translated) unread link title attribute
  *                            text string.
  * string  $mark_unread_uri   The URI of the script that performs the
  *                            mark-all-as-unread action (empty if the unread
  *                            link is not to be displayed).
  * string  $unread_onclick    The contents of the onclick handler for the
  *                            unread link, which may be empty if not in use.
  * boolean $square_brackets   Determines whether or not [] brackets (if
  *                            TRUE) or () brackets (if FALSE) will be
  *                            used aroung the link.
  *
  * Copyright (c) 2004-2005 Dave Kliczbor <maligree@gmx.de>
  * Copyright (c) 2003-2009 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2004 Ferdie Ferdsen <ferdie.ferdsen@despam.de>
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage mark_read
  *
  */


// retrieve the template vars
//
extract($t);


?><small>&nbsp;&nbsp;<?php if ($square_brackets) echo '['; else echo '('; if (!empty($mark_read_uri)) { ?><a href="<?php echo $mark_read_uri; ?>" title="<?php echo $read_title_text; ?>" style="text-decoration:none"<?php if (!empty($read_onclick)) echo ' onclick="' . $read_onclick . '"'; ?>><?php echo $read_text; ?></a><?php } if (!empty($mark_unread_uri)) { if (!empty($mark_read_uri)) { ?> | <?php } ?><a href="<?php echo $mark_unread_uri; ?>" title="<?php echo $unread_title_text; ?>" style="text-decoration:none"<?php if (!empty($unread_onclick)) echo ' onclick="' . $unread_onclick . '"'; ?>><?php echo $unread_text; ?></a><?php } if ($square_brackets) echo ']'; else echo ')'; ?></small><?php  /* no trailing whitespace so advanced folder tree in 1.5.2+ does not choke */
