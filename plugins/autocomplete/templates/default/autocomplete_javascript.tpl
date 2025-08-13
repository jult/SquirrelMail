<?php

/**
  * autocomplete_javascript.tpl
  *
  * Template for outputting JavaScript that implements the
  * auto-completion functionality for the Autocomplete plugin
  *
  * The following variables are available in this template:
  *
  * string addrsrch_fullname  Core SquirrelMail preference indicating
  *                           the format addresses should be added in:
  *                           "fullname" - use full name and email
  *                           "nickname" - use nickname and email
  *                           "noprefix" - only use email address
  * boolean autocomplete_match_case  TRUE when matching
  *                                  should be done only
  *                                  when case matches;
  *                                  FALSE for case-insensitive
  *                                  matching
  * boolean autocomplete_restrict_matching  TRUE when matching
  *                                         should only use the
  *                                         beginning of fields;
  *                                         FALSE allows matches
  *                                         anywhere
  * boolean autocomplete_preload  TRUE when all contacts are
  *                               in-page - do NOT use dynamic
  *                               searches; FALSE indicates
  *                               to use dynamic searching
  * boolean autocomplete_match_nicknames  TRUE when nickname
  *                                       matching is enabled
  * boolean autocomplete_match_fullnames  TRUE when full name
  *                                       matching is enabled
  * boolean autocomplete_match_emails     TRUE when email address
  *                                       matching is enabled
  * int autocomplete_minimum_number_characters  The number of characters
  *                                             that must be entered before
  *                                             we do any searching
  * boolean autocomplete_by_tab  TRUE when Tab presses should also
  *                              autocomplete like enter, etc.
  *                              FALSE to avoid autocompleting on Tab press
  * int  max_list_height  The maximum dropdown height or zero
  *                       if no maximum
  * string  javascript_contact_array  Contains a JavaScript-formatted array
  *                                   containing the list of contacts
  * boolean ac_debug  TRUE when this plugin is in debug mode
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
<script language="JavaScript" type="text/javascript">
<!--

<?php if (!$autocomplete_preload) { ?>
// Firefox, Chrome, Opera, Safari, IE7+
//
if (window.XMLHttpRequest)
   var xml_http_request = new XMLHttpRequest();

// IE5, IE6
//
else
   var xml_http_request = new ActiveXObject("Microsoft.XMLHTTP");

// reference to the dropdown object for which a search is pending
//
var dynamic_search_pending_dropdown = null;

// used to cache last search response
//
var dynamic_search_string = null;
var xml_http_request_response_text = null;
<?php } /* end if (!$autocomplete_preload) */ ?>


var dropdown_clicked_time = 0;


// sm_autocomplete constructor
// arguments:
//   array   aList  List of addresses
//   object  oText  The text input to attach to
function sm_autocomplete(aList, oText)
{
   // init members
   this.text_input = oText;
   this.dropdown_box = null;
   this.contact_list = aList;
   this.match_list = [];
   this.current_selection = 0;

   // attach to the text input
   oText.setAttribute('autocomplete', 'off');
   oText.sm_autocomplete = this;
   oText.onkeypress = this.on_key_press;
   oText.onkeyup = this.on_key_up;
   oText.onkeydown = this.on_key_down;
   oText.onmouseup = this.on_mouse_up;
   oText.onblur = this.on_text_blur;

   // this only results in duplication since every
   // focus will be followed with a mouseup or keyup
   //oText.onfocus = this.on_mouse_up;
}



// Handles a "keypress" - a character being typed.
// Herein, we only focus on characters that trigger the
// selection of an address from the dropdown (if any).
// Note that tab won't fire this event, so is handled
// in the keydown event instead.
//
// NOTE that "codes" are different between keypress
// and keyup/keydown events
//
// Attaches to text input, so 'this' is the text input
//
sm_autocomplete.prototype.on_key_press = function(e)
{
   var key_code = get_key_code(e);
   var separator = ',';
   switch (key_code)
   {
      case  59: // semicolon in mozilla
      case 186: // semicolon in msie
         separator = ';';
      case  44: // comma
      case  13: // enter
         if (this.sm_autocomplete.match_list.length > 0)
         {
            this.sm_autocomplete.complete_contact(separator);
            prevent_default(e);
         }
   }
}



// Herein, we only focus on keys that potentially
// move the dropdown selection with the one exception
// of the tab key, which doesn't fire the keypress
// event - it triggers selection of an address from
// the dropdown.
//
// NOTE that "codes" are different between keypress
// and keyup/keydown events
//
// Attaches to text input, so 'this' is the text input
//
sm_autocomplete.prototype.on_key_down = function(e)
{
   var key_code = get_key_code(e);
   switch (key_code)
   {
<?php if ($autocomplete_by_tab) { ?>
      case   9: // tab
         if (this.sm_autocomplete.match_list.length > 0)
         {
            this.sm_autocomplete.complete_contact(',');
            event = e || window.event;
            event.cancelBubble = true;
            event.returnValue = true;
            if (event.stopPropagation)
            {
               event.stopPropagation();
               event.preventDefault();
            }
            return false;
         }
         break;
<?php } ?>
      case  38: // up arrow
      case  40: // down arrow
         this.sm_autocomplete.select_contact(this.sm_autocomplete.current_selection + ((key_code == 38) ? -1 : 1));
   }
}



// Herein, we only focus on keys that potentially
// trigger a new address search (making sure to
// specifically ignore keys that have special
// meaning in other handlers)
//
// NOTE that "codes" are different between keypress
// and keyup/keydown events
//
// Attaches to text input, so 'this' is the text input
//
sm_autocomplete.prototype.on_key_up = function(e)
{
   var key_code = get_key_code(e);
   switch (key_code)
   {
      case  38: // up arrow
      case  40: // down arrow
      case 188: // comma
      case  13: // enter
      case   9: // tab
      case  59: // semicolon in mozilla
      case 186: // semicolon in msie
         // do nothing (dealt with elsewhere)
         break;
      default:
         this.sm_autocomplete.create_dropdown();
         break;
   }
}



// attaches to text input, so 'this' is the text input
//
sm_autocomplete.prototype.on_mouse_up = function(e)
{
   this.sm_autocomplete.create_dropdown();
}



// attaches to text input, so 'this' is the text input
//
sm_autocomplete.prototype.on_text_blur = function(e)
{
   // In some browsers, clicking on a scrollbar in the dropdown
   // causes loss of focus from the input text box - we need to
   // tell the blur handler not to hide the dropdown in this case.
   //
   // We detect this situation by the very short duration between
   // the click and blur events. (100 milliseconds)
   //
   if (new Date() - dropdown_clicked_time < 100)
   {
      this.focus(); // re-focus right back on the input - yikes! - but it works...
      return;
   }

   this.sm_autocomplete.hideDropdown();
}



// 'this' = current dropdown
//
sm_autocomplete.prototype.hideDropdown = function()
{
   if (this.dropdown_box != null)
      this.dropdown_box.style.display = 'none';

   this.match_list = [];
}



function is_alphanumeric(c)
{
   return (c >= "a" && c <= "z") || (c >= "A" && c <= "Z") || (c >= "0" && c <= "9");
}



// should prevent the default action from happening for both IE & Moz
//
function prevent_default(e)
{
   if (e && e.preventDefault)
      e.preventDefault();
   else
      event.returnValue = false;
}



// should get key code for both IE & Moz
//
function get_key_code(e)
{
   return e ? e.which : event.keyCode;
}



// Calculates the real position of an element
// on a page, either absolute or relative to
// page scroll
//
// object  element  The element whose position to calculate
//FIXME: broken in some cases (non-quirks?)
// boolean  relative_to_scroll  When true, position will be
//                              calculated relative to the
//                              current scrolled position
//
function get_element_position(element, relative_to_scroll)
{
   if (document.getElementById || document.all)
   {
      var top = 0;
      var left = 0;
      var element_parent_node = element;

      while (element)
      {
         top += element.offsetTop - (relative_to_scroll ? element.scrollTop : 0);
         left += element.offsetLeft - (relative_to_scroll ? element.scrollLeft : 0);
         element = element.offsetParent;
         if (relative_to_scroll)
         {
            element_parent_node = element_parent_node.parentNode;
            while (element_parent_node != element
                && typeof(element_parent_node.scrollTop) != 'undefined')
            {
               top -= element_parent_node.scrollTop;
               left -= element_parent_node.scrollLeft;
               element_parent_node = element_parent_node.parentNode;
            }
         }
      }
      return [left, top];
   }
   else if (document.layers)
      return [element.x, element.y];

   return [0, 0]; // uhhh...?
}
function OLD_get_element_position(element, relative_to_scroll)
{
   var top = 0;
   var left = 0;
   while (element)
   {
      top += element.offsetTop - (relative_to_scroll ? element.scrollTop : 0);
      left += element.offsetLeft - (relative_to_scroll ? element.scrollLeft : 0);
      element = element.offsetParent;
   }
   return [left, top];
}



// Determines the viewable window size
//
// Ideally, just check the top element's clientWidth/clientHeight,
// but some browsers don't like that, so unfortunately, this
// function usually won't account for scrollbars
//
function get_window_size()
{
   if (typeof(window.innerWidth) != 'undefined')
      return [window.innerWidth, window.innerHeight];

   element = document.compose; // intentionally not body
   while (1)
   {
      if (!element.offsetParent) break;
      element = element.offsetParent;
   }
   return [element.clientWidth, element.clientHeight];
}



// string match for autocomplete on full names
// find the first match of a substring not preceded by an alphanumeric char
//
function ac_match(str, sMatch)
{
   for (var i = 0; ; i++)
   {
      i = str.indexOf(sMatch, i);
      if (i < 0)
         return -1;

      if (i == 0 || !is_alphanumeric(str.charAt(i - 1)))
         return i;
   }
   return -1;
}



// compare given string against addressbook
// return list of matches
//
sm_autocomplete.prototype.find_matches = function(str)
{
   var matches = [];
   for (var i = 0; i < this.contact_list.length; i++)
   {
      var contact = this.contact_list[i];
<?php if ($autocomplete_restrict_matching) { ?>
      if (ac_use_nicks && (contact[0]<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>.indexOf(str) == 0) || 
          ac_use_addrs && (contact[2]<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>.indexOf(str) == 0) ||
          ac_use_names && (ac_match(contact[1]<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>, str) >= 0))
<?php } else { ?>
      if (ac_use_nicks && (contact[0]<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>.indexOf(str) >= 0) || 
          ac_use_addrs && (contact[2]<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>.indexOf(str) >= 0) ||
          ac_use_names && (contact[1]<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>.indexOf(str) >= 0))
<?php } ?>
      {
         matches[matches.length] = contact;
      }
   }
   return matches;
}



// FIXME: doesn't detect when separator is inside the quotes of a personal name
//
function get_contact_start(str, iStart)
{
   if (iStart <= 1)
      return 0;

   for (iStart-- ; iStart > 0; iStart--)
   {
      var c = str.charAt(iStart);
      if (c == ',' || c == ';')
      {
         iStart++;
         break;
      }
   }
   return iStart;
}



// FIXME: doesn't detect when separator is inside the quotes of a personal name
//
function get_contact_end(str, iEnd)
{
   for ( ; iEnd < str.length; iEnd++)
   {
      var c = str.charAt(iEnd);
      if (c == ',' || c == ';')
         break;
   }
   return iEnd;
}



// find the string we want to search the address book for
// according to where the cursor is in the text input
//
sm_autocomplete.prototype.get_match_string = function()
{
   var text = this.text_input.value;
   var pos = get_cursor_position(this.text_input);

   if (pos < 0)
      return "";

   var start = get_contact_start(text, pos);
   var end = get_contact_end(text,pos);

   // trim leading whitespace (not trailing, because
   // whitespace could be part of nickname or full name)
   //
   text = text.substr(start, end - start).replace(/^\s+/, "");

   return text;
}



// creates a new dropdown with contents
// corresponding to the current address search
//
// 'this' = current dropdown
//
sm_autocomplete.prototype.create_dropdown = function()
{
   if (this.dropdown_box == null)
   {
      this.dropdown_box = document.createElement('div');
      this.dropdown_box.id = 'acdropdown';
      this.dropdown_box.style.display = 'none';
      document.body.appendChild(this.dropdown_box);
      this.dropdown_box.onmousedown = function (e) { dropdown_clicked_time = new Date(); }
   }

   this.dropdown_box.style.display = 'none';
   pos = get_element_position(this.text_input, false);
   this.dropdown_box.style.left = pos[0] + "px";
   this.dropdown_box.style.top = (pos[1] + this.text_input.offsetHeight) + "px";
   this.dropdown_box.style.height = 'auto';
   this.dropdown_box.style.width = 'auto';
   this.dropdown_box.style.overflowX = 'hidden';

   // obtain text we need to search with
   //
   var match_str = this.get_match_string();
   if (match_str.length < <?php echo $autocomplete_minimum_number_characters; ?>)
      return;

   // empty the box
   //
   while (this.dropdown_box.hasChildNodes())
      this.dropdown_box.removeChild(this.dropdown_box.firstChild);

   // (re)fill the box
   //
<?php if ($autocomplete_preload) { ?>
   this.match_list = match_str ? this.find_matches(match_str) : [];
   this.populate_and_create_dropdown(match_str);
<?php } else { ?>
   dynamic_search(this, match_str);
<?php } ?>
}



// finishes creating a new dropdown with the given contents
//
// 'this' = current dropdown
//
sm_autocomplete.prototype.populate_and_create_dropdown = function(match_str)
{
   if (this.match_list.length)
   {
      var box = document.createElement('div');
      box.className = 'acbox';
      this.dropdown_box.appendChild(box);

      for (var i = 0; i < this.match_list.length; i++)
      {
         // note that we have to encode the match strings so that we boldify the right text
         //
         var html = '';
         // TODO: we could choose to show these fields even when not used in the matches, maybe on a configurable basis
         if (ac_use_nicks)
            html += '[' + boldify_match(ac_html_encode(this.match_list[i][0]), ac_html_encode(match_str)) + '] ';

         if (ac_use_names)
            html += '"' + boldify_match(ac_html_encode(this.match_list[i][1]), ac_html_encode(match_str)) + '" ';

         html += '&lt;' + (ac_use_addrs ? boldify_match(ac_html_encode(this.match_list[i][2]), ac_html_encode(match_str)) : ac_html_encode(this.match_list[i][2])) + '&gt;';

         var item = document.createElement('span');
         item.className = 'acitem';
         item.innerHTML = html;
         item.onmouseover = make_mouseover_function(this, i);
         item.onmousedown = make_mousedown_function(this, i);
         box.appendChild(item);
      }

      this.current_selection = 0;
      this.select_contact(0);


      this.dropdown_box.style.display = 'block';


      // make sure we are scrolled to the top of a new list
      // and don't let width or height go outside the visible window area
      //
      // (can't manipulate size/position until dropdown is being displayed, which
      // is why we do all this here, after display is set to "block")
      //
      // first determine the current approximate space available for the dropdown
      // (window size sadly probably doesn't account for scrollbars, so have to
      // add some margin to calculations below)
      //
      var relative_pos = get_element_position(this.text_input, true);
      var window_size = get_window_size();
      var max_dropdown_width = window_size[0] - relative_pos[0];
      var max_dropdown_height = window_size[1] - relative_pos[1];


      // next, set the scroll position and alter dropdown width if needed
      //
      this.dropdown_box.scrollTop = 0;

      if (this.dropdown_box.clientWidth > max_dropdown_width - 30) // give a little margin
      {
         // note for horizontal stuff, can't use css max-width and can't
         // set scroll to auto due to IE issues and not wanting a scrollbar
         // to appear when *vertical* one does
         //
         this.dropdown_box.style.overflowX = 'scroll';
         this.dropdown_box.style.width = (max_dropdown_width - 30) + "px";
      }

<?php if ($max_list_height) { ?>
      // set ideal list height
      //
      if (this.dropdown_box.clientHeight > <?php echo $max_list_height; ?>)
         this.dropdown_box.style.height = "<?php echo $max_list_height; ?>px";
<?php } ?>

      // if default height is too big for window...
      //
      if (this.dropdown_box.clientHeight > max_dropdown_height - 30) // give a little margin
         this.dropdown_box.style.height = (max_dropdown_height - 30) + "px";
   }
}



// make bold the matching portion of the string
//
function boldify_match(str, sMatch)
{
<?php if ($autocomplete_restrict_matching) { ?>
   var i = ac_match(str<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>, sMatch);
<?php } else { ?>
   var i = str<?php if (!$autocomplete_match_case) echo '.toLowerCase()'; ?>.indexOf(sMatch);
<?php } ?>
   if (i >= 0)
      return str.substr(0, i) + '<b>' + str.substr(i, sMatch.length) + '</b>' + str.substr(i + sMatch.length);
   else
      return str;
}



//TODO: we could provide an HTML-encoded version of the nickname, full name and email address directly from the server and not have to do this... is the bandwidth or client processing more valuable?  So far, this doesn't appear to slow down performance noticably, so leaving it
// HTML-encodes a string and returns it
//
function ac_html_encode(str)
{
   return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");   // replace(/'/g, "&#39;")
}



function make_mouseover_function(o, i)
{
   return function(e) { o.select_contact(i); };
}



function make_mousedown_function(o, i)
{
   return function(e) { o.select_contact(i); o.complete_contact(','); };
}



// moves the selector (highlighted row) in the dropdown list
//
sm_autocomplete.prototype.select_contact = function(idx)
{
   if (this.dropdown_box && this.dropdown_box.hasChildNodes()
    && idx >= 0 && idx < this.dropdown_box.firstChild.childNodes.length)
   {
      var nodes = this.dropdown_box.firstChild.childNodes;
      nodes[this.current_selection].className = "acitem";
      nodes[idx].className = "acitem sel";
      var node_height = nodes[idx].clientHeight;
      if (!node_height) node_height = 15; // ode to IE

      // if (bottom of current selection) went below visible area, scroll down
      //
      if (nodes[idx].offsetTop + node_height > this.dropdown_box.scrollTop + this.dropdown_box.clientHeight)
         this.dropdown_box.scrollTop += node_height;

      // if went above visible area, scroll up
      //
      else if (nodes[idx].offsetTop < this.dropdown_box.scrollTop)
         this.dropdown_box.scrollTop = nodes[idx].offsetTop;

      this.current_selection = idx;
   }
}



sm_autocomplete.prototype.complete_contact = function(c)
{
   var addr = "";
   if (this.match_list.length > 0)
   {
      var extra = <?php if ($addrsrch_fullname == 'fullname') echo 'this.match_list[this.current_selection][1]'; else if ($addrsrch_fullname == 'nickname') echo 'this.match_list[this.current_selection][0]'; else echo '""'; ?>;

      // If appears to be a list of addresses, we'll
      // only output them and withhold full or nickname
      if (extra != ""
       && this.match_list[this.current_selection][2].indexOf(';') == -1
       && this.match_list[this.current_selection][2].indexOf(',') == -1)
         addr += '"' + extra + '" <' + this.match_list[this.current_selection][2] + '>' + c;
      else
         addr += this.match_list[this.current_selection][2] + c;
   }

   var text = this.text_input.value;
   var pos = get_cursor_position(this.text_input);
   var start = get_contact_start(text, pos);
   var end = get_contact_end(text,pos);

   if (text.substr(end, 1) == c)
      end++;

   this.text_input.value = text.substr(0, start) + addr + text.substr(end);
   set_cursor_position(this.text_input, start + addr.length);
   this.hideDropdown();
}



function get_cursor_position(element)
{
   if (typeof element.selectionEnd != "undefined")
      return element.selectionEnd;

   else if (document.selection && document.selection.createRange)
   {
      var range = document.selection.createRange();
      if (range.parentElement() != element)
         return -1;

      var range2 = range.duplicate();
      range2.expand("textedit");
      range2.setEndPoint("EndToStart", range);
      if (range2.text.length > element.value.length)
         return -1;
      else
         return range2.text.length;
   }
   else
      return element.value.length;
}



function set_cursor_position(element, idx)
{
   if (typeof element.selectionEnd != "undefined")
   {
      element.selectionStart = idx;
      element.selectionEnd = idx;
   }
   else if (document.selection && document.selection.createRange)
   {
      var sel = element.createTextRange();
      sel.collapse(true);
      sel.move("character", idx);
      sel.select();
   }
}



<?php if (!$autocomplete_preload) { ?>
// handle finished dynamic search
//
function handle_dynamic_response()
{
   if (xml_http_request.readyState == 4 && xml_http_request.status == 200)
   {
<?php if ($ac_debug) { ?>
      console.log("SERVER RESPOSNE: " + xml_http_request.responseText);
<?php } ?>
      xml_http_request_response_text = xml_http_request.responseText;

      dynamic_search_pending_dropdown.match_list = eval(xml_http_request_response_text);
      dynamic_search_pending_dropdown.populate_and_create_dropdown(dynamic_search_string);
      // if we wanted to detect and display error messages,
      // they are provided in comments after an empty array
      //
      if (xml_http_request_response_text.indexOf("[] //ERROR: ") == 0)
      {
         alert("<?php echo _("Error searching address book:"); ?>\n\n   " + xml_http_request_response_text.substr(12));
      }

      dynamic_search_pending_dropdown = null;
      // don't reset the search string

   }
}



// make a dynamic search when we don't store all
// the user's abook entries in the page source
//
function dynamic_search(dropdown, str)
{
   // if nothing to search for, make sure other search isn't in progress
   //
   if (str == "")
   {
      xml_http_request.onreadystatechange = function() {};
      xml_http_request.abort();
      dropdown.match_list = [];
      return;
   }

   // if same search as last one, we already have results
   // (but only if the previous search was actually completed)
   //
   if (str == dynamic_search_string && dynamic_search_pending_dropdown == null)
   {
      dropdown.match_list = eval(xml_http_request_response_text);
      dropdown.populate_and_create_dropdown(str);
      return;
   }

   // could check readyState but anyhow we want to cancel no matter what
   //
   if (dynamic_search_pending_dropdown != null)
   {
      xml_http_request.onreadystatechange = function() {};
      xml_http_request.abort();
   }

   // which dropdown we're searching in and for what string
   //
   dynamic_search_pending_dropdown = dropdown;
   dynamic_search_string = str;

   xml_http_request.onreadystatechange = handle_dynamic_response;
   xml_http_request.open("GET", "<?php echo sqm_baseuri(); ?>plugins/autocomplete/abook_lookup.php?search=" + encodeURIComponent(str), true);
   xml_http_request.send();
}
<?php } /* end if (!$autocomplete_preload) */ ?>



<?php

   // include debug function when in debug mode
   //
   if ($ac_debug) { ?>

// this is a piece of crap way to debug
//
function ac_log(str)
{
   console.log(str);
   //var d = document.createElement('div');
   //d.innerHTML = str;
   //document.body.appendChild(d);
}

<?php }


   echo 'var autocomplete_contacts = ' . $javascript_contact_array . ";\n\n"
      . 'var ac_use_nicks = ' . ($autocomplete_match_nicknames ? '1' : '0') . ";\n"
      . 'var ac_use_names = ' . ($autocomplete_match_fullnames ? '1' : '0') . ";\n"
      . 'var ac_use_addrs = ' . ($autocomplete_match_emails ? '1' : '0') . ";\n";
?>

new sm_autocomplete(autocomplete_contacts, document.compose.send_to);
new sm_autocomplete(autocomplete_contacts, document.compose.send_to_cc);
new sm_autocomplete(autocomplete_contacts, document.compose.send_to_bcc);


// -->
</script>

