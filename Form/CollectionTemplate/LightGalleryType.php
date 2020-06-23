<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form\CollectionTemplate;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LightGalleryType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('thumbWidth', NumberType::class, [
            'label' => $this->translator->trans('Thumbnail width', [], 'cmfcmfmediamodule')
        ])
        ->add('thumbHeight', NumberType::class, [
            'label' => $this->translator->trans('Thumbnail height', [], 'cmfcmfmediamodule')
        ])
        ->add('thumbMode', ChoiceType::class, [
            'label' => $this->translator->trans('Thumbnail mode', [], 'cmfcmfmediamodule'),
            'choices' => [
                $this->translator->trans('inset', [], 'cmfcmfmediamodule') => 'inset',
                $this->translator->trans('outbound', [], 'cmfcmfmediamodule') => 'outbound'
            ],
        ])
        ->add('showTitleBelowThumbs', CheckboxType::class, [
            'label' => $this->translator->trans('Show the image titles below thumbnails.', [], 'cmfcmfmediamodule'),
            'required' => false
        ])
        ->add('showAttributionBelowThumbs', CheckboxType::class, [
            'label' => $this->translator->trans('Show the image attributions below thumbnails.', [], 'cmfcmfmediamodule'),
            'required' => false
        ]);
    }
}
