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
    /**
     * Array to store compressed data
     *
     * @var  array    $datasec
     */
    var $datasec      = array();

    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param  integer  the current Unix timestamp
     *
     * @return integer  the current date in a four byte DOS format
     *
     * @access private
     */
    function unix2DosTime($unixtime = 0) {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
        	$timearray['year']    = 1980;
        	$timearray['mon']     = 1;
        	$timearray['mday']    = 1;
        	$timearray['hours']   = 0;
        	$timearray['minutes'] = 0;
        	$timearray['seconds'] = 0;
        } // end if

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
                ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    } // end of the 'unix2DosTime()' method

    function addFile($data, $name, $time = 0) {
        $name     = substr(str_replace('\\', '/', $name),0,98);
        if (!$time)
           $time = time();
        $time = decoct($time);
        $mode = "100666";
        $uid = "0";
        $gid = "0";
        $x = strlen($data);
        $len = decoct($x);
        $chk  = "\x20\x20\x20\x20\x20\x20\x20";
        $fr   = $this->pad($name,100);              // filename
        $fr  .= $this->tspace($this->pad($mode,8));       // Mode
        $fr  .= $this->tspace($this->pads($uid,8));        // UID
        $fr  .= $this->tspace($this->pads($gid,8));        // GID
        $fr  .= $this->pads($len,12);                // Size
        $fr  .= $this->pads($time,12);           // last mod time and date
        $fr2  = "";
        $fr2 = $this->pad($fr2,513-(strlen($fr) + 8));
        $crc = decoct(checksum($fr . $chk . $fr2)) . "\x00 ";
        $fr = $this->pad($fr . $crc . $fr2,512);
        $x = strlen($data);
        $y = intval($x/512)+1;
        $x = ($y * 512) - $x;
        if ($x == 512) {
           $y--;
           $x = 0;
        }
        $fr .= $this->pad($data, $y * 512);
        $this -> datasec[] = $fr;
        unset($mode, $uid, $gid, $len, $chk);
        unset($fr, $fr2, $crc, $x, $y);

    } // end of the 'addFile()' method

    function tspace($var,$space = 2, $char = "\x00") {
       return substr($var,0,strlen($var)-$space) . " " . $char;
    }

    function pads($var, $pad,$s = true) {
       if ($s) $t = 2; else $t =0;
       while (strlen($var) < $pad-$t)
          $var = " " . $var;
       if ($s) return $var . "  ";
       return $var;
    }   

    function pad($var, $pad) {
       while (strlen($var) < $pad)
          $var .= "\x00";
       return $var;
    }

    function file() {
        global $mailbox;
        $filename = archive_replace_str($mailbox, '-') . '.tar';
        $data = implode('', $this->datasec);
        $time = time();
        $dtime    = dechex($time);
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
                  . '\x' . $dtime[4] . $dtime[5]
                  . '\x' . $dtime[2] . $dtime[3]
                  . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');
        $zdata   = gzdeflate($data,9);
        $unc_len = strlen($data);
        $crc     = crc32($data);

        $fr    = "\x1f\x8b";            // ID1 & ID2
        $fr   .= "\x08\x08";            // compression method & flags
        $fr   .= $hexdtime;             // last mod time and date
        $fr   .= "\x00\x00";            // XFL & OS
        $fr   .= "$filename" . "\x00";
        $fr   .= $zdata;                // Compressed Blocks
        $fr   .= pack('V', $crc);       // crc32
        $fr   .= pack('V', $unc_len);   // uncompressed filesize
        unset($data, $zdata);
        return $fr;
    } // end of the 'file()' method

} // end of the 'zipfile' class

function sendheader ($filename) {
   $encoding = gzip_accepted();
   header("Content-Type: application/$encoding");
   if (USR_BROWSER_AGENT == 'IE') {
      header('Content-Disposition: inline; filename="' . $filename . '.tar.gz"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
   } else {
      header('Content-Disposition: attachment; filename="' . $filename . '.tar.gz"');
      header('Expires: 0');
      header('Pragma: no-cache');
   }
}

global $HTTP_ACCEPT_ENCODING;
function gzip_accepted() {
   global $HTTP_ACCEPT_ENCODING;
   if (strpos($HTTP_ACCEPT_ENCODING, 'gzip') === false) return 'gzip';
   if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') === false)
      $encoding = 'gzip';
   else
      $encoding = 'x-gzip';
   return $encoding;
}


function checksum ($t) {
   $x = 0;
   $b = unpack('C*', $t);
   while (list ($l, $h) = each ($b)) $x += $h;
   return $x % 0xffff;
}

?>