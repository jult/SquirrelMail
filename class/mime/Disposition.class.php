<?php

/**
 * Disposition.class.php
 *
 * This file contains functions needed to handle content disposition headers 
 * in mime messages. See RFC 2183.
 *
 * @copyright 2003-2020 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: Disposition.class.php 14840 2020-01-07 07:42:38Z pdontthink $
 * @package squirrelmail
 * @subpackage mime
 * @since 1.3.2
 */

/**
 * Class that handles content disposition header
 * @package squirrelmail
 * @subpackage mime
 * @since 1.3.0
 * @todo FIXME: do we have to declare vars ($name and $properties)?
 */
class Disposition {
    /**
     * Constructor (PHP5 style, required in some future version of PHP)
     * @param string $name
     */
    function __construct($name) {
       $this->name = $name;
       $this->properties = array();
    }

    /**
     * Constructor (PHP4 style, kept for compatibility reasons)
     * @param string $name
     */
    function Disposition($name) {
       self::__construct($name);
    }

    /**
     * Returns value of content disposition property
     * @param string $par content disposition property name
     * @return string
     * @since 1.3.1
     */
    function getProperty($par) {
        $value = strtolower($par);
        if (isset($this->properties[$par])) {
            return $this->properties[$par];
        }
        return '';
    }
}

