<?php

namespace App\V1\Cart\Exception;

class CartNotFoundException extends \App\Exception
{
    /**
     * CartNotFoundException constructor.
     * Very simple one, as details are not really needed here
     */
    public function __construct()
    {
        parent::__construct('', 0, null);
    }
}
