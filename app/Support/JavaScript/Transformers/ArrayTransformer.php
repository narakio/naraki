<?php

namespace App\Support\JavaScript\Transformers;

class ArrayTransformer
{
    /**
     * Transform an array.
     *
     * @param  array $value
     * @return string
     */
    public function transform($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }
    }
}