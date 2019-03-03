<?php

/**
  * SquirrelMail Quote of the Day at Login Plugin
  *
  * Copyright (c) 2003-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002 Tracy McKibben <tracy@mckibben.d2g.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage qotd_login
  *
  */


global $fortune_command, $qotdSource, $qotd_org_address,
       $qotd_org_cache_seconds, $qotd_login_debug;



// Where should quotes be retrieved from?
//
//    0 = Autodetect (quotes will be pulled randomly from either 
//        qotd.org or the fortune program, assuming both exist (this
//        plugin is smart enough to know if one or the other does
//        not exist))
//
//    1 = Always pull quotes from qotd.org
//
//    2 = Always pull quotes from the fortune program (see below)
//
$qotdSource = 0;



// This is the command that will be run when quotes
// are pulled from the fortune program
//
// If you have problems when the fortune command is
// being executed, you can include error output with
// something similar to the following:
//
// $fortune_command = '/usr/bin/fortune -s 2>&1';
//
$fortune_command = '/usr/games/fortune -as';



// Where should qotd.org quotes be retrieved from?  This normally
// does not need to be changed.
//
$qotd_org_address = 'http://www5.qotd.org/sm/';



// How long should quotes retrieved from qotd.org be cached?
// This value is in seconds, so:
//
//     1 hour  = 3600 seconds
//     4 hours = 14400 seconds
//     1 day   = 86400 seconds
//
$qotd_org_cache_seconds = 3600;



// This can help diagnose certain problems, such as
// understanding when there are connection problems
// to qotd.org
//
//    0 (zero) = hide debugging information (normal operation)
//    1        = show any debugging information
//
$qotd_login_debug = 0;



