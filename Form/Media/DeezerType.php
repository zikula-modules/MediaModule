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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeezerType extends WebType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['hiddenFields'] = [
            'title', 'url', 'license'
        ];
        parent::buildForm($builder, $options);

        $builder
            ->add('musicType', 'hidden')
            ->add('musicId', 'hidden')
        ;
        if ($options['showPlaylistCheckbox']) {
            $builder->add('showPlaylist', 'checkbox', [
                'label' => $this->translator->trans('Show playlist', [], 'cmfcmfmediamodule'),
                'required' => false
            ]);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setRequired('showPlaylistCheckbox');
    }
}
