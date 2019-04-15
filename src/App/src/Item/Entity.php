<?php

namespace App\Item;

use Zend\Stdlib\ArraySerializableInterface;

class Entity implements ArraySerializableInterface
{
    /**
     * @var string|int
     */
    private $id;

    /**
     * An unit price, always an int
     * @var int
     */
    private $unitPrice;

    /**
     * @var int
     */
    private $qty;

    /**
     * Exchange internal values from provided array
     *
     * @param array $array
     * @return void
     */
    public function exchangeArray(array $array): void
    {
        $this->id = $array['id'] ?? 0;
        $this->unitPrice = (int) abs($array['unit_price'] ?? 0);
        $this->qty = (int) abs($array['qty'] ?? 1);
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        return [
            'id'         => $this->id,
            'unit_price' => $this->unitPrice,
            'qty'        => $this->qty
        ];
    }
}
