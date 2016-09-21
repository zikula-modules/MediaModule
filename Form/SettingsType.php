<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Form;

use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SettingsType extends SymfonyAbstractType
{
    /**
     * @var array
     */
    private $templates;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator, array $templates)
    {
        $this->templates = $templates;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('descriptionEscapingStrategyForCollection', 'choice', [
                'label' => $this->translator->trans('Collection description escaping strategy', [], 'cmfcmfmediamodule'),
                'required' => true,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection'),
                'choices' => [
                    'text' => $this->translator->trans('Safe - no HTML permitted, only plain text', [], 'cmfcmfmediamodule'),
                    'markdown' => $this->translator->trans('MarkDown', [], 'cmfcmfmediamodule'),
                    'raw' => $this->translator->trans('As is - use with editors like Scribite', [], 'cmfcmfmediamodule'),
                ]
            ])
            ->add('descriptionEscapingStrategyForMedia', 'choice', [
                'label' => $this->translator->trans('Media description escaping strategy', [], 'cmfcmfmediamodule'),
                'required' => true,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForMedia'),
                'choices' => [
                    'text' => $this->translator->trans('Safe - no HTML permitted, only plain text', [], 'cmfcmfmediamodule'),
                    'markdown' => $this->translator->trans('MarkDown', [], 'cmfcmfmediamodule'),
                    'raw' => $this->translator->trans('As is - use with editors like Scribite', [], 'cmfcmfmediamodule'),
                ]
            ])
            ->add('defaultCollectionTemplate', 'cmfcmfmediamodule_collectiontemplate', [
                'label' => $this->translator->trans('Default collection template', [], 'cmfcmfmediamodule'),
                'required' => true,
                'allowDefaultTemplate' => false,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'defaultCollectionTemplate'),
            ])
            // @todo Allow to edit slugs.
            //->add('slugEditable', 'checkbox', [
            //    'label' => $this->translator->trans('Make slugs editable', [], 'cmfcmfmediamodule'),
            //    'data' => \ModUtil::getVar('CmfcmfMediaModule', 'slugEditable')
            //])
            ->add('soundCloudApiKey', 'text', [
                'label' => $this->translator->trans('SoundCloud "Client ID"', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'soundCloudApiKey'),
                'attr' => [
                    'help' => $this->translator->trans('Go to http://soundcloud.com/you/apps/new and create a new application. The name doesn\'t matter. In the next screen, enter the url of your Zikula installation at "Website of your App" and leave "Redirect URI for Authentication" empty. Then save and paste the "Client ID" here.', [], 'cmfcmfmediamodule')
                ]
            ])
            // @todo Flickr currently disabled.
            //->add('flickrApiKey', 'text', [
            //    'label' => $this->translator->trans('Flickr API Client Key', [], 'cmfcmfmediamodule'),
            //    'required' => false,
            //    'empty_data' => null,
            //    'data' => \ModUtil::getVar('CmfcmfMediaModule', 'flickrApiKey'),
            //    'attr' => [
            //        'help' => $this->translator->trans('Go to https://www.flickr.com/services/apps/create/apply and create a new application. The name doesn\'t matter. Paste the "Key" here (not the "secret key", [], 'cmfcmfmediamodule').')
            //    ]
            //])
            ->add('googleApiKey', 'text', [
                'label' => $this->translator->trans('Google API Developer Key', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'googleApiKey'),
                'attr' => [
                    'help' => $this->translator->trans('Go to https://console.developers.google.com/project and create a new project. The name and id don\'t matter. Then go to "APIs and Authentication -> APIs" and enable the "YouTube Data API v3". Then go to "APIs and Authentication -> Credentials" and click "Add credentials -> API-Key -> Server-Key". Again, the name does\'t matter. Then paste the API key here.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('twitterApiKey', 'text', [
                'label' => $this->translator->trans('Twitter API Consumer Key', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiKey'),
                'attr' => [
                    'help' => $this->translator->trans('Go to https://apps.twitter.com/ and create a new application. The name doesn\'t matter and "Callback URL" should be empty. Then go to "Keys and Access Tokens". At the bottom, click at "Create my access token".', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('twitterApiSecret', 'text', [
                'label' => $this->translator->trans('Twitter API Secret', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiSecret')
            ])
            ->add('twitterApiAccessToken', 'text', [
                'label' => $this->translator->trans('Twitter API Access Token', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessToken')
            ])
            ->add('twitterApiAccessTokenSecret', 'text', [
                'label' => $this->translator->trans('Twitter API Access Token Secret', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessTokenSecret')
            ])
            ->add('save', 'submit', [
                'label' => $this->translator->trans('Save', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cmfcmfmediamodule_settingstype';
    }
}
