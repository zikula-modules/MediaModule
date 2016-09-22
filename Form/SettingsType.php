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

use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

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

    /**
     * @var VariableApi
     */
    private $variableApi;

    public function __construct(TranslatorInterface $translator, VariableApi $variableApi, array $templates)
    {
        $this->templates = $templates;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EntityManagerInterface $em */
        $em = \ServiceUtil::get('doctrine.orm.entity_manager');

        $builder
            ->add('descriptionEscapingStrategyForCollection', 'choice', [
                'label' => $this->translator->trans('Collection description escaping strategy', [], 'cmfcmfmediamodule'),
                'required' => true,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection'),
                'choices' => [
                    'text' => $this->translator->trans('Safe - no HTML permitted, only plain text', [], 'cmfcmfmediamodule'),
                    'markdown' => $this->translator->trans('MarkDown', [], 'cmfcmfmediamodule'),
                    'raw' => $this->translator->trans('As is - use with editors like Scribite', [], 'cmfcmfmediamodule'),
                ]
            ])
            ->add('descriptionEscapingStrategyForMedia', 'choice', [
                'label' => $this->translator->trans('Media description escaping strategy', [], 'cmfcmfmediamodule'),
                'required' => true,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'descriptionEscapingStrategyForMedia'),
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
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'defaultCollectionTemplate'),
            ])
            ->add('defaultLicense', 'entity', [
                'label' => $this->translator->trans('Default license', [], 'cmfcmfmediamodule'),
                'required' => false,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'defaultLicense', null),
                'class' => 'CmfcmfMediaModule:License\LicenseEntity',
                'preferred_choices' => function (LicenseEntity $license) {
                    return !$license->isOutdated();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.title', 'ASC');
                },
                'empty_data' => null,
                'placeholder' => $this->translator->trans('Unknown', [], 'cmfcmfmediamodule'),
                'property' => 'title',
            ])
            // @todo Allow to edit slugs.
            //->add('slugEditable', 'checkbox', [
            //    'label' => $this->translator->trans('Make slugs editable', [], 'cmfcmfmediamodule'),
            //    'data' => $this->variableApi->get('CmfcmfMediaModule', 'slugEditable')
            //])
            ->add('enableMediaViewCounter', 'checkbox', [
                'label' => $this->translator->trans('Enable media view counter', [], 'cmfcmfmediamodule'),
                'required' => false,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'enableMediaViewCounter', false),
                'attr' => [
                    'help' => $this->translator->trans('Please note that this will cause an additional database update query per page view. Be also aware that the "updated date" and "updated user" fields will be updated every time as well.')
                ]
            ])
            ->add('enableCollectionViewCounter', 'checkbox', [
                'label' => $this->translator->trans('Enable collection view counter', [], 'cmfcmfmediamodule'),
                'required' => false,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'enableCollectionViewCounter', false),
                'attr' => [
                    'help' => $this->translator->trans('Please note that this will cause an additional database update query per page view. Be also aware that the "updated date" and "updated user" fields will be updated every time as well.')
                ]
            ])
            ->add('soundCloudApiKey', 'text', [
                'label' => $this->translator->trans('SoundCloud "Client ID"', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'soundCloudApiKey'),
                'attr' => [
                    'help' => $this->translator->trans('Go to http://soundcloud.com/you/apps/new and create a new application. The name doesn\'t matter. In the next screen, enter the url of your Zikula installation at "Website of your App" and leave "Redirect URI for Authentication" empty. Then save and paste the "Client ID" here.', [], 'cmfcmfmediamodule')
                ]
            ])
            // @todo Flickr currently disabled.
            //->add('flickrApiKey', 'text', [
            //    'label' => $this->translator->trans('Flickr API Client Key', [], 'cmfcmfmediamodule'),
            //    'required' => false,
            //    'empty_data' => null,
            //    'data' => $this->variableApi->get('CmfcmfMediaModule', 'flickrApiKey'),
            //    'attr' => [
            //        'help' => $this->translator->trans('Go to https://www.flickr.com/services/apps/create/apply and create a new application. The name doesn\'t matter. Paste the "Key" here (not the "secret key", [], 'cmfcmfmediamodule').')
            //    ]
            //])
            ->add('googleApiKey', 'text', [
                'label' => $this->translator->trans('Google API Developer Key', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'googleApiKey'),
                'attr' => [
                    'help' => $this->translator->trans('Go to https://console.developers.google.com/project and create a new project. The name and id don\'t matter. Then go to "APIs and Authentication -> APIs" and enable the "YouTube Data API v3". Then go to "APIs and Authentication -> Credentials" and click "Add credentials -> API-Key -> Server-Key". Again, the name does\'t matter. Then paste the API key here.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('googleApiOAuthClientID', 'text', [
                'label' => $this->translator->trans('Google API OAuth2 Client ID', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'googleApiOAuthClientID'),
                'attr' => [
                    'help' => $this->translator->trans('Go to https://console.developers.google.com/project and create a new project. The name and id don\'t matter. Then go to "APIs and Authentication -> APIs" and enable the "YouTube Data API v3". Then go to "APIs and Authentication -> Credentials" and click "Add credentials -> OAuth-Client-ID -> Webapplication". Again, the name does\'t matter. Then paste the Client-ID here.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('googleApiOAuthClientSecret', 'text', [
                'label' => $this->translator->trans('Google API OAuth2 Client Secret', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'googleApiOAuthClientSecret'),
                'attr' => [
                    'help' => $this->translator->trans('Use the OAuth Client-Secret you got when creating your OAuth Client-ID.', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('twitterApiKey', 'text', [
                'label' => $this->translator->trans('Twitter API Consumer Key', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiKey'),
                'attr' => [
                    'help' => $this->translator->trans('Go to https://apps.twitter.com/ and create a new application. The name doesn\'t matter and "Callback URL" should be empty. Then go to "Keys and Access Tokens". At the bottom, click at "Create my access token".', [], 'cmfcmfmediamodule')
                ]
            ])
            ->add('twitterApiSecret', 'text', [
                'label' => $this->translator->trans('Twitter API Secret', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiSecret')
            ])
            ->add('twitterApiAccessToken', 'text', [
                'label' => $this->translator->trans('Twitter API Access Token', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessToken')
            ])
            ->add('twitterApiAccessTokenSecret', 'text', [
                'label' => $this->translator->trans('Twitter API Access Token Secret', [], 'cmfcmfmediamodule'),
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessTokenSecret')
            ])
            ->add('save', 'submit', [
                'label' => $this->translator->trans('Save', [], 'cmfcmfmediamodule'),
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
        $builder->get('defaultLicense')
            ->addModelTransformer(new CallbackTransformer(function ($modelData) use ($em) {
                if (null === $modelData) {
                    return null;
                }

                return $em->find('CmfcmfMediaModule:License\LicenseEntity', $modelData);
            }, function ($viewData) {
                /** @var null|LicenseEntity $viewData */
                if (null === $viewData) {
                    return null;
                }

                return $viewData->getId();
            }));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cmfcmfmediamodule_settingstype';
    }
}
