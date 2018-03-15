<?php
/*******************************************************************************

    Author ......... Jimmy Conner
    Contact ........ jimmy@advcs.org
    Home Site ...... http://www.advcs.org/
    Program ........ Archive Mail
    Version ........ 1.2
    Purpose ........ Allows you to download your email in a compressed archive

*******************************************************************************/

class zipfile {
    var $datasec      = array();

    function addFile($data, $name, $time = 0) {
        $this -> datasec[] = $data;
    }

    function file() {
        return implode('', $this -> datasec);
    }

}

function sendheader ($filename) {
      header('Content-Type: plain/text');
      if (USR_BROWSER_AGENT == 'IE') {
         header('Content-Disposition: attachment; filename="' . $filename . '.txt"');
         header('Expires: 0');
         header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
         header('Pragma: public');
      } else {
         header('Content-Disposition: attachment; filename="' . $filename . '.txt"');
         header('Expires: 0');
         header('Pragma: no-cache');
      }
}

?>