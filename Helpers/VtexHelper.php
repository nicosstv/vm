<?php


namespace Vtex\VtexMagento\Helpers;


class VtexHelper
{


    /**
     * @param object $totals
     * @param string $key
     * @param boolean $valueOnly
     * @param boolean $formatValue
     * @return object|int
     */
    public function getTotals($totals, $key, $valueOnly = false, $formatValue = false)
    {

        if (isset($totals) && $totals && isset($key) && $key) {
            foreach ($totals as $total):
                if ($total->id === $key):
                    $output = $total;
                    if ($valueOnly) {
                        $output = $formatValue ? $this->formatValue($total->value) : $total->value;
                    }
                    return $output;
                endif;

            endforeach;
        }

        return 0;
    }

    /**
     * @param $value
     * @return float
     */
    public function formatValue($value)
    {
        return (float)str_replace('-', '', substr_replace($value, '.', (strlen($value) - 2), 0));
    }

    /**
     * @param object $products
     * @return int
     */
    public function getTotalProductsQuantity($products)
    {

        $quantity = 0;

        if ($products && $products):
            foreach ($products as $product):
                $quantity += $product->quantity;
            endforeach;
        endif;

        return $quantity;

    }

    /**
     * @param object $products
     * @return int|float
     */
    public function getTotalWeight($products)
    {
        $weight = 0;

        if ($products && $products):
            foreach ($products as $product):
                $weight += $product->additionalInfo->dimension->weight;
            endforeach;
        endif;


        return $weight;

    }

}
