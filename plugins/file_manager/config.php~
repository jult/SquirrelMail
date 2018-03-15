<?php

   global $file_manager_config, $defaultFilePerms, $defaultFolderPerms,
          $systemUmask, $symlinkColor, $fileEditStyle, $fileLinkColor, 
          $chmodOK, $newlineChar, $globalAllowEditBinary, $sharedDirectoryQuotas,
          $antiVirusCommand, $antiVirusCommandFoundNoVirusReturnCode,
          $checkUploadsForVirii, $showAntiVirusErrorDetails,
          $showGoodAntiVirusResults, $viewFilesBehavior;



   $systemUmask = '0022';

   $defaultFilePerms   = '0644';
   $defaultFolderPerms = '0755';


   // set to '' if symlinks should not be different color
   //
   $symlinkColor = 'red';


   // to edit a file, there can be an edit link or the
   // file itself can be a hyperlink... make your choice
   // here
   //
   $fileEditStyle = 'edit link';
   //$fileEditStyle = 'hyperlink';


   // If the above is set to 'hyperlink', then you can
   // choose the color of those hyperlinks here.  Set 
   // to '' if they be regular link colors.
   //
   $fileLinkColor = 'black';



   // view links for files will show the file in the same 
   // window or a separate (new/pop-up) window depending on
   // this setting.  users may override this behavior
   // individually if this is set to 1 or 2.
   //
   //     1     defaults to new window, but user can override
   //     2     defaults to same window as file manager, but user can override
   //     3     new window
   //     4     same window as file manager
   //
   $viewFilesBehavior = 1;



   // indicates if chmod is available on the host system
   // (and support is compiled in to php).  This should
   // be disabled if no chmod commands should be run 
   // whatsoever (even when it is turned on, you can 
   // disable access to chmod for individual users as
   // per above).  Most users of this plugin won't need 
   // to change this setting - those who do probably know 
   // who you are.  
   //
   $chmodOK = 1;



   // this is a global override that allows the sysadmin to
   // turn off binary file edit permission for all users,
   // regardless of the individual users' settings
   // (if set to 1, the individual user settings are obeyed)
   //
   $globalAllowEditBinary = 1;



   // this should be set to the newline character you expect
   // for text files on your system, typically that will be:
   //
   // Macintosh:  <CR>      ->  \r    ->  13     ->  \015
   // Windows:    <CR><LF>  ->  \r\n  ->  13 10  ->  \015\012
   // *nix:       <LF>      ->  \n    ->  10     ->  \012
   //
   // (Note - make sure you use double quotation marks here!)
   //
   //$newlineChar = "\015";   // Mac
   //$newlineChar = "\015\012";   // Windows
   $newlineChar = "\012";   // *nix



   // anti-virus command integration for file uploads:
   //
   // The command for your anti-virus scanner should be entered
   // into the variable $antiVirusCommand using "%filename%"
   // (without the quotes) where the uploaded filename will be
   // substituted.
   //
   // $antiVirusCommandFoundNoVirusReturnCode must be set to
   // the expected return value of that command when no virus
   // has been found during the scan.  Any other return code
   // will result in the user being unable to upload the file.
   //
   // $checkUploadsForVirii should be set to 1 to turn on virus
   // checking.
   //
   // When $showAntiVirusErrorDetails is set to 1, the user
   // will see the output of the anti-virus command when a
   // virus has been found.
   //
   // Finally, $showGoodAntiVirusResults allows an informational
   // report-back of the anti-virus scan when no viruses were
   // found.  Set it to 1 to turn the report on.
   //
   $antiVirusCommand = '';
   $antiVirusCommandFoundNoVirusReturnCode = 0;
   $checkUploadsForVirii = 0;
   $showAntiVirusErrorDetails = 1;
   $showGoodAntiVirusResults = 0;



   // shared directories can have their quotas specified
   // separately, in which case they always override a
   // single user's own quota.  Note that the directories
   // listed here need not be base directories - they can
   // be at any level of the directory hierarchy.
   //
   $sharedDirectoryQuotas = array(

// change the samples below as necessary and make sure to 
// remove the comment markers at the beginning of the line!

//      '/home/html' => '10GB',
//      '/var/shared' => '150MB',

   );



//==========================================================================

   // NOTE!  Everything below this line is for legacy support (although it 
   //        works in conjunction with the file_manager.users file if 
   //        needed) - unless you are continuing not to migrate to the
   //        file_manager.users file,  this section should remain commented 
   //        out!
   //
   // users and their base directories and permissions...
   //
/*
   $file_manager_config = array(
      'user1@some.domain.com' => array(
         'baseDir1' => '/home/some.domain.com/web',
         'quota' => '5MB',
         'adminMail' => 'webmaster@some.domain.com',
         'allowLinks' => 0,
         'allowChmod' => 0,
         'allowEditBinary' => 0
      ),

      'user2@another.domain.net' => array(
         'baseDir1' => '/www/user2/html',
         'quota' => '5MB',
         'adminMail' => 'help@yet.another.domain.org',
         'allowLinks' => 0,
         'allowChmod' => 0,
         'allowEditBinary' => 0
      ),

      'veryimportantuser@another.domain.net' => array(
         'baseDir1' => '/',
         'baseDir2' => '/home/some/other/directory',
         'baseDir3' => '/yet/another/directory',
         'quota' => '',
         'adminMail' => 'help@yet.another.domain.org',
         'allowLinks' => 1,
         'allowChmod' => 1,
         'allowEditBinary' => 1
      )

   );
*/


?>
