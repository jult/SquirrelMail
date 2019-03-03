<?php

/*
 * Imap functions (stolen from src/view_header.php)
 */

function parse_showheader($imapConnection,$id, $passed_ent_id) {
  global $uid_support;

  $header_full = array();
  if (!$passed_ent_id) {
    $read=sqimap_run_command ($imapConnection, "FETCH $id BODY[HEADER]", 
                              true, $a, $b, $uid_support);
  } else {
    $query = "FETCH $id BODY[".$passed_ent_id.'.HEADER]';
    $read=sqimap_run_command ($imapConnection, $query, true, 
                              $a, $b, $uid_support);
  }    

  $cnum = 0;
  for ($i=1; $i < count($read); $i++) {
    $line = htmlspecialchars($read[$i]);
    switch (true) {
      case (eregi("^&gt;", $line)):
        $second[$i] = $line;
        $first[$i] = '&nbsp;';
        $cnum++;
        break;
      case (eregi("^[ |\t]", $line)):
        $second[$i] = $line;
        $first[$i] = '';
        break;
      case (eregi("^([^:]+):(.+)", $line, $regs)):
        $first[$i] = $regs[1] . ':';
        $second[$i] = $regs[2];
        $cnum++;
        break;
      default:
        $second[$i] = trim($line);
        $first[$i] = '';
        break;
    }
  }
  for ($i=0; $i < count($second); $i = $j) {
    $f = (isset($first[$i]) ? $first[$i] : '');
    $s = (isset($second[$i]) ? nl2br($second[$i]) : ''); 
    $j = $i + 1;
    while (($first[$j] == '') && ($j < count($first))) {
      $s .= '&nbsp;&nbsp;&nbsp;&nbsp;' . nl2br($second[$j]);
      $j++;
    }
    if(strtolower($f) != 'message-id:') {
      parseEmail($s);
    }
    if ($f) {
      $header_output[] = array($f,$s);
    }
  }

  return $header_output;
}

/*
 * Support Functions
 */

function print_array($array, $level) {
  if (!is_array($array) && !is_object($array))
    return 0;

  reset($array);
  
  while (list($key, $value) = each($array)) {
    for ($i = 0; $i < $level; ++$i) {
      echo '&nbsp;&nbsp;&nbsp;';
    }

    echo $key . ' = ' . $value . '<BR>';

    if (is_array($value) || is_object($value))
      print_array($value, $level+1);
  }
}

/*
 * Plugin Functions
 */

function show_headers_options_display() {
  global $show_header_array;

  echo '<tr><td colspan="2">&nbsp;</td></tr>' .
  '<tr>'.
  '<td align="right" valign="top">' . _("Show Headers:") .
  '<BR>' . _("(One Per Line)") . '</td>' .
  '<td align="left" valign="top">'.
  '<textarea name="show_header_settings">';

  if (isset($show_header_array))
    echo implode("\r\n", $show_header_array);
  
  echo '</textarea>' .
  '</td>' .
  '</tr>';
}

function show_headers_options_save() {
  global $username, $data_dir, $show_header_array;

  $temp = '';

  if (!check_php_version(4,1)) {
    global $_POST;
  }

  if (isset($_POST['show_header_settings'])) {
    $temp = $_POST['show_header_settings'];
    
    if (strlen($temp) > 0) {
      $show_header_array = explode("\r\n", $temp);

      foreach($show_header_array as $key => $header) {
        if (strlen($header) == 0) {
          unset($show_header_array[$key]);
        }
      }

      if (count($show_header_array) == 0) {
        unset($show_header_array);
      }
    } else {
      unset($show_header_array);
    }
  } else {
    unset($show_header_array);
  }

  if (isset($show_header_array)) {
    setPref($data_dir, $username, 'show_headers', 
            implode(";", $show_header_array));
  } else {
    setPref($data_dir, $username, 'show_headers', '');
  }
}

function show_headers_load() {
  global $username, $data_dir, $show_header_array;

  $temp = '';

  $temp = getPref($data_dir, $username, 'show_headers');

  if (strlen($temp) > 0) {
    $show_header_array = explode(";", $temp);
  } else {
    unset($GLOBALS['show_header_array']);
  }
}


function show_headers_display() {
  global $show_header_array, $imapConnection, $passed_id, $passed_ent_id;

  $headers = array();
  $found = array();

  if (!isset($show_header_array))
    return 0;

  $headers = parse_showheader($imapConnection, $passed_id, $passed_ent_id);

  foreach($show_header_array as $search) {
    foreach($headers as $header) {
      if (preg_match("/" . $search . "/i", $header[0])) {
        $found[] = '<tr><td align="right">' . $header[0] . 
                    '&nbsp;</td><td align="left">' . 
                    eregi_replace("(<br[^>]*>)|(\r)|(\n)", "", decodeHeader($header[1],false,false,true)) .
                    '</td></tr>';
      }
    }   
  }
  
  if (count($found) > 0 ) { 
    echo '<tr>' .
          '<td align="right" valign="top" width="20%">' .
          '<B>' . _("Headers:") . '&nbsp;&nbsp;</B></td>' .
          '<td align="left" valign="middle">' .
          '<table border="0" cellpadding="0" cellspacing="0">' .
          implode("", $found) . 
          '</table></td></tr>';
  }

  return 0;
}

?>
