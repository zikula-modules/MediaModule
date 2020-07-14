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

namespace Cmfcmf\Module\MediaModule\Form;

use Cmfcmf\Module\MediaModule\CollectionTemplate\TemplateCollection;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\Form\CollectionTemplate\TemplateType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class SettingsType extends SymfonyAbstractType
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $templates;

    public function __construct(
        VariableApiInterface $variableApi,
        EntityManagerInterface $em,
        TemplateCollection $templateCollection
    ) {
        $this->variableApi = $variableApi;
        $this->em = $em;
        $this->templates = $templateCollection->getCollectionTemplateTitles();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('descriptionEscapingStrategyForCollection', ChoiceType::class, [
                'label' => 'Collection description escaping strategy',
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection'),
                'choices' => [
                    'Safe - no HTML permitted, only plain text' => 'text',
                    'MarkDown' => 'markdown',
                    'As is - use with editors like Scribite' => 'raw'
                ]
            ])
            ->add('descriptionEscapingStrategyForMedia', ChoiceType::class, [
                'label' => 'Media description escaping strategy',
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'descriptionEscapingStrategyForMedia'),
                'choices' => [
                    'Safe - no HTML permitted, only plain text' => 'text',
                    'MarkDown' => 'markdown',
                    'As is - use with editors like Scribite' => 'raw'
                ]
            ])
            ->add('defaultCollectionTemplate', TemplateType::class, [
                'label' => 'Default collection template',
                'required' => true,
                'allowDefaultTemplate' => false,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'defaultCollectionTemplate'),
            ])
            ->add('defaultLicense', EntityType::class, [
                'label' => 'Default license',
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
                'placeholder' => 'Unknown',
                'choice_label' => 'title',
            ])
            /*@todo Allow to edit slugs.
            ->add('slugEditable', CheckboxType::class, [
               'label' => 'Make slugs editable',
               'data' => $this->variableApi->get('CmfcmfMediaModule', 'slugEditable')
            ])*/
            ->add('enableMediaViewCounter', CheckboxType::class, [
                'label' => 'Enable media view counter',
                'required' => false,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'enableMediaViewCounter', false),
                'help' => 'Please note that this will cause an additional database update query per page view. Be also aware that the "updated date" and "updated user" fields will be updated every time as well.'
            ])
            ->add('enableCollectionViewCounter', CheckboxType::class, [
                'label' => 'Enable collection view counter',
                'required' => false,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'enableCollectionViewCounter', false),
                'help' => 'Please note that this will cause an additional database update query per page view. Be also aware that the "updated date" and "updated user" fields will be updated every time as well.'
            ])
            ->add('soundCloudApiKey', TextType::class, [
                'label' => 'SoundCloud "Client ID"',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'soundCloudApiKey'),
                'help' => 'Create a new application <a href="https://soundcloud.com/you/apps/new" target="_blank">here</a>. The name doesn\'t matter. In the next screen, enter the url of your Zikula installation at "Website of your App" and leave "Redirect URI for Authentication" empty. Then save and paste the "Client ID" here.',
                'help_html' => true
            ])
            /*@todo Flickr currently disabled.
            ->add('flickrApiKey', TextType::class, [
               'label' => 'Flickr API Client Key',
               'required' => false,
               'empty_data' => null,
               'data' => $this->variableApi->get('CmfcmfMediaModule', 'flickrApiKey'),
               'help' => 'Create a new application <a href="https://www.flickr.com/services/apps/create/apply" target="_blank">here</a>. The name doesn\'t matter. Paste the "Key" here (not the "secret key".',
                'help_html' => true)
            ])*/
            ->add('googleApiKey', TextType::class, [
                'label' => 'Google API Developer Key',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'googleApiKey'),
                'help' => 'Create a new project <a href="https://console.developers.google.com/project" target="_blank">here</a>. The name and id don\'t matter. Then go to "APIs and Authentication -> APIs" and enable the "YouTube Data API v3". Then go to "APIs and Authentication -> Credentials" and click "Add credentials -> API-Key -> Server-Key". Again, the name does\'t matter. Then paste the API key here.',
                'help_html' => true
            ])
            ->add('googleApiOAuthClientID', TextType::class, [
                'label' => 'Google API OAuth2 Client ID',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'googleApiOAuthClientID'),
                'help' => 'After you created the project for "Google API Developer Key" above go to "APIs and Authentication -> Credentials" again and click "Add credentials -> OAuth-Client-ID -> Webapplication". Again, the name does\'t matter. Then paste the Client-ID here.',
                'help_html' => true
            ])
            ->add('googleApiOAuthClientSecret', TextType::class, [
                'label' => 'Google API OAuth2 Client Secret',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'googleApiOAuthClientSecret'),
                'help' => 'Use the OAuth Client Secret you got when creating your OAuth Client ID.'
            ])
            ->add('twitterApiKey', TextType::class, [
                'label' => 'Twitter API Consumer Key',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiKey'),
                'help' => 'Create a new application <a href="https://apps.twitter.com/" target="_blank">here</a>. The name doesn\'t matter and "Callback URL" should be empty. Then go to "Keys and Access Tokens". At the bottom, click at "Create my access token".',
                'help_html' => true
            ])
            ->add('twitterApiSecret', TextType::class, [
                'label' => 'Twitter API Secret',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiSecret')
            ])
            ->add('twitterApiAccessToken', TextType::class, [
                'label' => 'Twitter API Access Token',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessToken')
            ])
            ->add('twitterApiAccessTokenSecret', TextType::class, [
                'label' => 'Twitter API Access Token Secret',
                'required' => false,
                'empty_data' => null,
                'data' => $this->variableApi->get('CmfcmfMediaModule', 'twitterApiAccessTokenSecret')
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
        $em = $this->em;
        $builder->get('defaultLicense')
            ->addModelTransformer(new CallbackTransformer(function ($modelData) use ($em) {
                if (null === $modelData) {
                    return null;
                }

                return $em->find(LicenseEntity::class, $modelData);
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
    public function getBlockPrefix()
    {
        return 'cmfcmfmediamodule_settingstype';
    }
}
