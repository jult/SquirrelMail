<?php

/**
  * autocomplete_stylesheet.tpl
  *
  * Template for outputting CSS for the Autocomplete plugin
  *
  * The following variables are available in this template:
  *
  * array  color  The standard SquirrelMail color themes array
  * int  max_list_height  The maximum dropdown height or zero
  *                       if no maximum
  *
  * @copyright &copy; 1999-2011 The SquirrelMail Project Team
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version $Id$
  * @package squirrelmail
  * @subpackage plugins
  */


// retrieve the template vars
//
extract($t);


?>
<style type="text/css">
.acitem {
   color: <?php echo $color[8]; ?>;
   cursor: pointer;
   white-space: nowrap;
}

/* each entry in an address list */
.acbox span {
   text-decoration: none;
   color: <?php echo $color[8]; ?>;
   display: block;
   cursor: default;
   padding: 0px 10px 0px 10px;
   font-size: 90%;
   white-space: nowrap;
}

/* highlighting of the currently selected row in the dropdown */
.acbox span.sel {
   background: <?php echo $color[4]; ?>;
}

/* the whole dropdown div */
#acdropdown {
   position: absolute;
   z-index: 3;
   border: 1px <?php echo $color[8]; ?> outset;
   background: <?php echo $color[0]; ?>;
   padding: 2px;
<?php if ($max_list_height) { ?>
   max-height: <?php echo $max_list_height; ?>px;
   overflow-y: auto;
<?php } ?>
   overflow-x: hidden;
}
</style>

