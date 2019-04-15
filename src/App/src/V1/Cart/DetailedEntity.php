<?php

namespace App\V1\Cart;

class DetailedEntity extends Entity
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * Exchange internal values from provided array
     * @param array $array
     * @return void
     */
    public function exchangeArray(array $array): void
    {
        parent::exchangeArray($array);
        $this->items = $array['items'] ?? [];
    }

    /**
     * Return an array representation of the object
     * @return array
     */
    public function getArrayCopy(): array
    {
        $items = 0;
        $value = 0;
        foreach ($this->items as $item) {
            $items += $item['qty'];
            $value += $item['unit_price'] * $item['qty'];
        }

        return parent::getArrayCopy() + [
            'items' => $this->items,
            'total_items' => $items,
            'total_value' => $value,
            'total_value_in_eur' => bcdiv((string) $value, (string) 100, 2)
        ];
    }
}
