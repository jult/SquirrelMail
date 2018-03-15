<?php

/**
 * Disposition.class.php
 *
 * This file contains functions needed to handle content disposition headers 
 * in mime messages. See RFC 2183.
 *
 * @copyright 2003-2018 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: Disposition.class.php 14749 2018-01-16 23:36:07Z pdontthink $
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

