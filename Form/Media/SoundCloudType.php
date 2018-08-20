<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class SoundCloudType extends WebType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['hiddenFields'] = [
            'url', 'license'
        ];
        parent::buildForm($builder, $options);

        $builder
            ->add('musicType', HiddenType::class)
            ->add('musicId', HiddenType::class)
        ;
    }
}
