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

use Cmfcmf\Module\MediaModule\CollectionTemplate\SelectedTemplateFactory;
use Cmfcmf\Module\MediaModule\CollectionTemplate\TemplateCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateType extends AbstractType implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    /**
     * @var TemplateCollection
     */
    private $templateCollection;

    /**
     * @var SelectedTemplateFactory
     */
    private $selectedTemplateFactory;

    public function __construct(
        TemplateCollection $templateCollection,
        SelectedTemplateFactory $selectedTemplateFactory
    ) {
        $this->templateCollection = $templateCollection;
        $this->selectedTemplateFactory = $selectedTemplateFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $selectedTemplateFactory = $this->selectedTemplateFactory;

        $builder
            ->add('template', ChoiceType::class, [
                'label' => 'Template',
                'required' => !$options['allowDefaultTemplate'],
                'placeholder' => $options['allowDefaultTemplate'] ? 'Default' : false,
                'choices' => $this->templateCollection->getCollectionTemplateTitles()
            ])
            ->add('options', FormType::class, [
                'required' => false
            ])
            ->addModelTransformer(
                new CallbackTransformer(function ($modelData) use ($selectedTemplateFactory) {
                    if (null === $modelData) {
                        return [
                            'template' => null,
                            'options' => []
                        ];
                    }
                    $selectedTemplate = $selectedTemplateFactory->fromDB($modelData);

                    return [
                        'template' => $selectedTemplate->getTemplate()->getName(),
                        'options' => $selectedTemplate->getOptions()
                    ];
                }, function ($viewData) use ($selectedTemplateFactory) {
                    if (null === $viewData['template']) {
                        return null;
                    }

                    return $selectedTemplateFactory->fromTemplateName($viewData['template'], (array) $viewData['options'])->toDB();
                })
            )
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if (null === $data) {
                    $form->add('options', FormType::class, [
                        'required' => false
                    ]);

                    return;
                }

                $selectedTemplate = $this->selectedTemplateFactory->fromDB($data);

                $settingsForm = $selectedTemplate->getTemplate()->getSettingsForm();
                if (null !== $settingsForm) {
                    $form->add('options', $settingsForm);
                }
            })
        ;

        $builder->get('template')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $template = $form->getData();
            if (null === $template) {
                $form->getParent()->add('options', FormType::class, [
                    'required' => false
                ]);

                return;
            }
            $selectedTemplate = $this->selectedTemplateFactory->fromDB($template);
            $settingsForm = $selectedTemplate->getTemplate()->getSettingsForm();
            if (null !== $settingsForm) {
                $form->getParent()->add('options', $settingsForm);
            } else {
                $form->getParent()->add('options', FormType::class, [
                    'required' => false
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('allowDefaultTemplate', true);
    }

    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_collectiontemplate';
    }
}
