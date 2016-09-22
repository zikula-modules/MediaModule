<?php

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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class TemplateType extends AbstractType implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TemplateCollection
     */
    private $templateCollection;

    /**
     * @var SelectedTemplateFactory
     */
    private $selectedTemplateFactory;

    public function __construct(TranslatorInterface $translator, TemplateCollection $templateCollection, SelectedTemplateFactory $selectedTemplateFactory)
    {
        $this->translator = $translator;
        $this->templateCollection = $templateCollection;
        $this->selectedTemplateFactory = $selectedTemplateFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $selectedTemplateFactory = $this->selectedTemplateFactory;

        $builder->add('template', 'choice', [
            'label' => $this->translator->trans('Template', [], 'cmfcmfmediamodule'),
            'required' => !$options['allowDefaultTemplate'],
            'placeholder' => $options['allowDefaultTemplate'] ? $this->translator->trans('Default', [], 'cmfcmfmediamodule') : false,
            'choices' => $this->templateCollection->getCollectionTemplateTitles()
        ])->add('options', 'form', [
            'required' => false
        ])->addModelTransformer(new CallbackTransformer(function ($modelData) use ($selectedTemplateFactory) {
            if ($modelData === null) {
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
        }))->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (null === $data) {
                $form->add('options', 'form', [
                    'required' => false
                ]);

                return;
            }

            $selectedTemplate = $this->selectedTemplateFactory->fromDB($data);

            $settingsForm = $selectedTemplate->getTemplate()->getSettingsForm();
            if (null !== $settingsForm) {
                $form->add('options', $settingsForm);
            }
        });

        $builder->get('template')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $template = $form->getData();
            if (null === $template) {
                $form->getParent()->add('options', 'form', [
                    'required' => false
                ]);

                return;
            }
            $selectedTemplate = $this->selectedTemplateFactory->fromDB($template);
            $settingsForm = $selectedTemplate->getTemplate()->getSettingsForm();
            if (null !== $settingsForm) {
                $form->getParent()->add('options', $settingsForm);
            } else {
                $form->getParent()->add('options', 'form', [
                    'required' => false
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('allowDefaultTemplate', true);
    }

    public function getName()
    {
        return "cmfcmfmediamodule_collectiontemplate";
    }
}