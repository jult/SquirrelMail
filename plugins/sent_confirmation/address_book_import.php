<?PHP

  /**
   **  address_book_import.php
   **    
   **  Copyright (c) 1999-2004 The SquirrelMail development team
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **            
   **    Import addresses from a sent message
   **      This takes any number of email addresses (as post variables
   **      titled 'address1', 'address2', etc) and processes them for 
   **      importing to the addressbook.  The user may rearrange the
   **      field order and change any of the data.
   **
   **   This was a part of the Squirrelmail source tree that was modified
   **   for use with the address book import/export plugin, and was 
   **   further mauled for use here.
   **/

   $colsize = 15;


   chdir('..');
   define('SM_PATH','../');

   
   // include compatibility plugin
   //
   if (defined('SM_PATH'))
      include_once(SM_PATH . 'plugins/compatibility/functions.php');
   else if (file_exists('../plugins/compatibility/functions.php'))
      include_once('../plugins/compatibility/functions.php');
   else if (file_exists('./plugins/compatibility/functions.php'))
      include_once('./plugins/compatibility/functions.php');


   if (compatibility_check_sm_version(1, 3))
   {
      include_once (SM_PATH . 'include/validate.php');
      include_once (SM_PATH . 'functions/page_header.php');
      include_once (SM_PATH . 'include/load_prefs.php');
      include_once (SM_PATH . 'functions/addressbook.php');
   }
   else
   {
      include_once ('../src/validate.php');
      include_once ('../functions/page_header.php');
      include_once ('../src/load_prefs.php');
      include_once ('../functions/addressbook.php');
   }


   if (compatibility_check_sm_version(1, 3))
      include_once (SM_PATH . 'plugins/sent_confirmation/config.php');
   else
      include_once ('../plugins/sent_confirmation/config.php');


   // get global variable for versions of PHP < 4.1
   //
   if (!compatibility_check_php_version(4,1)) {
      global $HTTP_POST_VARS;
      $_POST = $HTTP_POST_VARS;
   }


   bindtextdomain('sent_confirmation', SM_PATH . 'plugins/sent_confirmation/locale');
   textdomain('sent_confirmation');


   // Local Variables
   $errorstring = '';
   $finish = '';
   $csvmax = 5;
   $key = 0;
   $x = 0;
   $row = 0;
   $cols = 0;
   $colspan = 0;
   $c = 0;
   $error = 0;
   $reorg = array();
   $selrow = '';
   $addresses = array();

   global $color, $emailAddressDelimiter;
   
   // Grab POST variables
   global $finish;
   compatibility_sqextractGlobalVar('finish');


// Here we will split the script into finished and not finished parts 
if (!$finish) {

   foreach ($_POST as $varName => $varValue)
   {
      if (strpos($varName, 'address') !== FALSE)
         $addresses[] = urldecode($varValue);
   }

   displayPageHeader($color, "None");

   // These shouldn't be set at all at this point. 
   $ADDRS['csvdata'] = array(); 
   $ADDRS['csvorder'] = array();

   foreach ($addresses as $addr) { 

      list($address, $nick) = explode('---', $addr);
      if (empty($nick))
         $nick = substr($address, 0, strpos($address, $emailAddressDelimiter));
      if (preg_match('/(\w+), (\w+)/', $nick, $matches))
         $temp = array($nick, $matches[2], $matches[1], $address, '');
      else if (preg_match('/(\w+)[_ ](\w+)/', $nick, $matches))
         $temp = array($nick, $matches[1], $matches[2], $address, '');
      else
         $temp = array($nick, $nick, '', $address, '');
      
      $ADDRS['csvdata'][$key] = $temp;
      $key++;

   }

   unset($ADDRS['csvorder']);

   echo '
   <FORM METHOD="post" action="' . $PHP_SELF . '">
   <CENTER><TABLE WIDTH="95%" FRAME="void" CELLSPACING="1">
   <TR><TD colspan="5"><strong>' . _("Add To Address Book:") . '</strong><br><br></TD></TR>
   ';    // user's data table

   // Here I will create the headers that I want.
   echo '
   <TR BGCOLOR="' . $color[9] . '" ALIGN="center">
   ';
   //<TD WIDTH="1">' .  _("Omit") . '</TD>
   //'; // The Omit column

   for($x = 0; $x < $csvmax; $x++) { // The Drop down boxes to select what each column is
      echo '<TD>';
      create_Select($csvmax,$x); 
      echo '</TD>
      ';
   }

   echo '</TR>
   ';

   do {
      if (count($ADDRS['csvdata'][$row]) >= 5) {    // This if ensures the minimum number of columns
         $cols = count($ADDRS['csvdata'][$row]);    // so importing can function for all 5 fields
      } else {
         $cols = 5;
      }        

      $colspan = $cols + 1;
      if ($row % 2) {                   // Set up the alternating colored rows
         echo '<TR BGCOLOR="' . $color[0] . '">
         ';
      } else {
         echo '<TR>
         ';
      }

      //echo '<TD WIDTH="1"><INPUT TYPE="checkbox" NAME="sel' . $row . '">
      //'; // Print the omit checkbox, to be checked before write

      for($c = 0; $c < $cols; $c++) { // For each column in the current row
         if ($ADDRS['csvdata'][$row][$c] != "") {                                // if not empty, put data in cell.
            echo '<TD ALIGN="CENTER" NOWRAP><INPUT SIZE="' . $colsize . '" NAME="data' . $row . '_' . $c . '" VALUE="' . $ADDRS['csvdata'][$row][$c] . '"></TD>
            ';
         } else {                                          // if empty, put space in cell keeping colors correct.
            echo '<TD ALIGN="CENTER"><INPUT SIZE="' . $colsize . '" NAME="data' . $row . '_' . $c . '"></TD>
            ';
         }
      }
      echo '</TR>
      ';
      $row++;
   } while ($row < count($ADDRS['csvdata']));
   
   echo '
   <TR><TD colspan="5"><br><INPUT TYPE="submit" NAME="finish" VALUE="' . _("Add") . '"></TD></TR>
   </TABLE>
   </CENTER>
   ';

   if(strlen($errorstring)) {   
      echo _("The following rows have errors") . ': <p>
      ' . $errorstring;
   }
   
} else {
   /** 
    **   $abook ---->Setup the addressbook functions for Pallo's Addressbook.
    **/
   $abook = addressbook_init(true, true); // We only need to do this here because we will only access the address book in this section

   // rebuild submit data
   //
   foreach ($_POST as $varName => $varValue)
   {
      preg_match('/^data(\d)_(\d)$/', $varName, $matches);
      if (count($matches))
      {
         $ADDRS['csvdata'][$matches[1]][$matches[2]] = $varValue;
      }
   }


   do {
      if (count($ADDRS['csvdata'][$row]) >= 5) {    // This if ensures the minimum number of columns
         $cols = count($ADDRS['csvdata'][$row]);    // so importing can function for all 5 fields
      } else {
         $cols = 5;
      }  
            
      $reorg = array('', '', '', '', '');
      
      for ($c=0; $c < $cols; $c++) {
         // Reorganize the data to fit the header cells that the user chose
         // concatenate fields based on user input into text boxes.
         $column = "COL$c";
         
         if($_POST[$column] != 5)  {
            if ($_POST[$column] == 4) {
               $reorg[4] .= $ADDRS['csvdata'][$row][$c] . ";";
            } else {
               $reorg[$_POST[$column]] = $ADDRS['csvdata'][$row][$c];
               $reorg[$c] = trim($reorg[$c],"\r\n \"");
            }
         }
      }
      
      $reorg[4] = trim($reorg[4],";");
      $ADDRS['csvdata'][$row] = $reorg;
      unset($reorg); // So that we don't get any weird information from a previous rows

      // If finished, do the import. This uses Pallo's excellent class and object stuff 
      $selrow = 'sel' . $row;
      
      if (!isset($_POST[$selrow])) {
         if (preg_match('[ \\:\\|\\#\\"\\!]', $ADDRS['csvdata'][$row][0])) {
            $ADDRS['csvdata'][$row][0] = '';
         }

         //Here we should create the right data to input 
         if (count($ADDRS['csvdata'][$row]) < 5) {
            array_pad($ADDRS['csvdata'][$row],5,'');
         }

         $addaddr['nickname'] 	= $ADDRS['csvdata'][$row][0];
         $addaddr['firstname'] 	= $ADDRS['csvdata'][$row][1];
         $addaddr['lastname'] 	= $ADDRS['csvdata'][$row][2];
         $addaddr['email'] 	= $ADDRS['csvdata'][$row][3];
         $addaddr['label'] 	= $ADDRS['csvdata'][$row][4];

         if (false == $abook->add($addaddr,$abook->localbackend)) {
            $errorstring .= $abook->error . "<br>\n";
            $error++;
         }

         unset($addaddr); // Also so we don't get any weird information from previous rows
      }
      
      $row++;
      
   } while($row < count($ADDRS['csvdata']));

   unset($ADDRS['csvdata']); // Now that we've uploaded this information, we dont' need this variable anymore, aka cleanup

   // Print out that we've completed this operation
   if ($error) {
      // Since we will print something to the page at this point
      displayPageHeader($color, "None");

      echo '<BR>' . _("There were errors uploading the data, as listed below. Entries not listed here were uploaded.") . '<br> ' . $errorstring . '<BR> ';
   } else {
      header('Location: ../../src/addressbook.php');
      exit;
      // Since we will print something to the page at this point
      displayPageHeader($color, "None");

      echo '<BR><BR><H1><STRONG><CENTER>' . _("Upload Completed!") . '</STRONG></H1>' . _("Click on the link below to verify your work.") . '</CENTER>';
   }

   echo '<BR><BR><CENTER><A HREF="../../src/addressbook.php">' . _("Addresses") . '</A></CENTER>
   ';
}

   // Send the field numbers entered in the text boxes by the user back to this script for more processing
   // email is handled differently, not being an array
function create_Select($csvmax,$column) {
   // $column is the one that should be selected out of the bunch
   echo "<SELECT NAME=\"COL$column\">\n";

   if($column > 5)
    $column = 5; // So we have only our normal choices. 
    
   for($temp = 0; $temp <= 5; $temp++) {
      echo "<OPTION value=$temp ";
      if ($column==$temp)
        echo "SELECTED";
      if ($temp == 0)
        echo ">" . _("Nickname") . "</option>\n";
      if ($temp == 1)
        echo ">" . _("First Name") . "</option>\n";
      if ($temp == 2)
        echo ">" . _("Last Name") . "</option>\n";
      if ($temp == 3)
        echo ">" . _("Email") . "</option>\n";
      if ($temp == 4)
        echo ">" . _("Additional Info") . "</option>\n";
      if ($temp == 5)
        echo ">" . _("Do Not Include") . "</option>\n";
   }
   echo "</select>\n";
}


   bindtextdomain('squirrelmail', SM_PATH . 'locale');
   textdomain('squirrelmail');


?>
</FORM>
</BODY>
</HTML>
