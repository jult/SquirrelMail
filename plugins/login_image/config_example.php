<?php

/**
  * SquirrelMail Random Login Image Plugin
  *
  * Copyright (c) 2011-2011 Paul Lesniewski <paul@squirrelmail.org>
  * Copyright (c) 2002 Tracy McKibben <tracy@mckibben.d2g.com>
  *
  * Licensed under the GNU GPL. For full terms see the file COPYING.
  *
  * @package plugins
  * @subpackage login_image
  *
  */


global $login_image_override_org_logo, $local_image_directory,
       $local_image_ignore_list, $local_image_files_cache_seconds,
       $local_image_file_height, $login_image_directory_separator,
       $login_image_verify_image_links, $login_image_remote_image_sources;



// Should the image displayed by this plugin be used in place of
// the "org logo" that is set in the main SquirrelMail configuration?
// If not, note that the image displayed by this plugin will be
// IN ADDITION to any "org logo" that is configured in SquirrelMail.
//
//    1 = yes, override "org logo" (only display one image)
//    0 = no, do not override "org logo" (display two images)
//
$login_image_override_org_logo = 1;



// This is the path to a directory on the local server
// that contains images from which to choose.  It should
// be a relative file path, relative to the SquirrelMail
// "src/" directory, ready to be used directly in a HTML
// image tag.
//
// If you leave this setting empty, this plugin will not
// attempt to use local files as a source.
//
$local_image_directory = '../plugins/login_image/images';



// If you'd like to size all the local image files to the
// same display size, you can specify an image height (in
// pixels) here and it will be applied to ALL images in
// the local image source.  If you set this to 0 (zero),
// the images will be displayed at their native size.
//
$local_image_file_height = 0;



// When loading the image files from the local source, what
// file names should be ignored?  You may use the file globbing
// wildcard characters * (zero or more characters) and ? (one
// character) as needed.
//
// Note that this list is case insensitive ("index" will find
// "INDEX", "Index" and "index")
//
$local_image_ignore_list = array('.', '..', 'index.*');



// If you'd like to cache the list of local image files for
// better performance, set this value to the number of seconds
// the list should be kept for (86400 seconds in a day, 604800
// seconds in a week, approximately 2592000 seconds in a month
// (30 days)).
//
// If this value is set to 0 (zero), the local image file list
// will not be cached at all.
//
// Note that when you add or remove images to/from this location,
// you may want to refresh the cache listing.  To do this, simply
// set this value to 1, load the login page, and then return this
// setting to what it was.
//
$local_image_files_cache_seconds = 604800;  // one week



// You can add as many remote image sources as you'd like.
// Each remote source should have the following elements
// in it:
//
//    "address"  This is the location of the web page that
//               contains the desired image (usually, you
//               want to pick web sites that change the
//               target image frequently).
//    "parse_pattern"  This is the regular expression pattern
//                     that is used to extract only the image
//                     address out of the web page.
//    "pattern_group_number"  The "parse_pattern" should capture
//                            the needed image address in a set
//                            of parenthesis; this element tells
//                            us which set of parenthesis it is.
//    "image_address_prefix"  When images are given as relative
//                            links in a web page, this element
//                            can be used to prepend the website's
//                            domain to it.  This is OPTIONAL.
//    "image_height"  If you'd like the target image to be sized
//                    up or down, you may specify a pixel height
//                    here (width will be proportional).  If you
//                    want to display the image at its original
//                    size, set this to 0 (zero) or just leave it
//                    out.  This is OPTIONAL.
//    "cache_seconds"  This specifies how long you'd like to keep
//                     this image address cached.  This prevents
//                     the plugin from reading and parsing the web
//                     page every time the login page is loaded.
//                     For images that change daily, 86400 seconds
//                     is reasonable (24 hours).  
//
// Keep in mind that if you have many remote sources, you may see
// a slight drop in performance (depending somewhat on "cache_seconds"
// and the value of $login_image_verify_image_links below).
//
// Here are a few examples that turned up in a cursory Internet
// search for "daily image":
//
// $login_image_remote_image_sources = array(
//    array(
//       'address'              => 'http://www.nnvl.noaa.gov/imageoftheday.php',
//       'parse_pattern'        => '/src=\'(images\/low_resolution\/.+?)\' alt=/',
//       'pattern_group_number' => 1,
//       'image_address_prefix' => 'http://www.nnvl.noaa.gov/',
//       'image_height'         => 300,
//       'cache_seconds'        => 86400,
//    ),
//    array(
//       'address' => 'http://www.antarctica.ac.uk/images/daily/image.php?numCategoryID=1',
//       'parse_pattern'        => '/<link rel="image_src" href="(.+?)" \/>/',
//       'pattern_group_number' => 1,
//       'image_height'         => 0, // or leave this out as the next one
//       'cache_seconds'        => 86400,
//    ),
//    array(
//       'address'              => 'http://www.antarctica.ac.uk/images/daily/image.php',
//       'parse_pattern'        => '/<link rel="image_src" href="(.+?)" \/>/',
//       'pattern_group_number' => 1,
//       'cache_seconds'        => 86400,
//    ),
//    array(
//       'address'              => 'http://wvs.topleftpixel.com',
//       'parse_pattern' => '/<td><div align="left" class="blogbody"><p><img alt=".+?src="(.+?)"/',
//       'pattern_group_number' => 1,
//       'image_height'         => 300,
//       'cache_seconds'        => 86400,
//    ),
//    array(
//       'address'              => 'http://www.crestock.com/free-image.aspx',
//       'parse_pattern'        => '/<img id="ctl00_CPHContent_imgPreview" src="(.+?)"/',
//       'pattern_group_number' => 1,
//       'image_address_prefix' => 'http://www.crestock.com/',
//       'image_height'         => 300,
//       'cache_seconds'        => 86400,
//    ),
// );
//
// Leaving this blank as below means this plugin will only
// attempt to use local image sources.
//
$login_image_remote_image_sources = array();



// Should this plugin verify that all remote image addresses
// are valid, even when they have been cached?  Turning this
// feature on can help guarantee that image addresses don't
// go bad but this comes at a performance sacrifice, which
// can be noticable depending on how many remote sources are
// being used.
//
//    1 = yes, always verify remote image addresses
//    0 = no, do not verify remote images (best performance)
//
$login_image_verify_image_links = 0;



// This is the directory separator character for your system.
// It is usually '/' on Linux, Unix and Mac OS type systems
// and '\' on Windows type systems.  Moreover, '/' will work
// in most versions of PHP on Windows.
//
$login_image_directory_separator = '/';



