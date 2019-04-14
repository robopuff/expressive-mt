<?php

namespace App\V1\Cart;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;

class Collection extends Paginator
{
    /**
     * Collection constructor.
     * @param array $documents
     */
    public function __construct(array $documents)
    {
        parent::__construct(new ArrayAdapter($documents));
    }
}
