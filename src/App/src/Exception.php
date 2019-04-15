<?php

namespace App;

/**
 * It's easier in the end to have a one master exception if it'll be used anywhere else
 * or just like a library, if you want to catch'em all (but limit to this code only)
 */
class Exception extends \Exception
{
}
