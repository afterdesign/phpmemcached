<?php

//I'm lazy and i know i should use spl_autoload_register to do it nicely

set_include_path(get_include_path() . PATH_SEPARATOR . "./");
function __autoload($className) {
    require_once(str_replace('\\', '/', $className).".php");
}