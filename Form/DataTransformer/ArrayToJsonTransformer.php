<?php

namespace Cmfcmf\Module\MediaModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class ArrayToJsonTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return json_encode($value);
    }

    public function reverseTransform($value)
    {
        return json_decode($value, true);
    }
}
