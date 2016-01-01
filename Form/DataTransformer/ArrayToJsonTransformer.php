<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms an array to JSON.
 */
class ArrayToJsonTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return json_encode($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return json_decode($value, true);
    }
}
