<?php

namespace Cmfcmf\Module\MediaModule\Form;

use Symfony\Component\Form\AbstractType as SymfonyAbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\I18n\TranslatableInterface;

class SettingsType extends SymfonyAbstractType implements TranslatableInterface
{
    protected $domain;

    /**
     * @var array
     */
    private $templates;

    public function __construct(array $templates)
    {
        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
        $this->templates = $templates;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('descriptionEscapingStrategyForCollection', 'choice', [
                'label' => $this->__('Collection description escaping strategy'),
                'required' => true,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForCollection'),
                'choices' => [
                    'text' => $this->__('Safe - no HTML permitted, only plain text'),
                    'markdown' => $this->__('MarkDown'),
                    'raw' => $this->__('As is - use with editors like Scribite'),
                ]
            ])
            ->add('descriptionEscapingStrategyForMedia', 'choice', [
                'label' => $this->__('Media description escaping strategy'),
                'required' => true,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForMedia'),
                'choices' => [
                    'text' => $this->__('Safe - no HTML permitted, only plain text'),
                    'markdown' => $this->__('MarkDown'),
                    'raw' => $this->__('As is - use with editors like Scribite'),
                ]
            ])
            ->add('defaultCollectionTemplate', 'choice', [
                'label' => $this->__('Default collection template'),
                'required' => true,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'defaultCollectionTemplate'),
                'choices' => $this->templates
            ])
            // @todo Allow to edit slugs.
            //->add('slugEditable', 'checkbox', [
            //    'label' => $this->__('Make slugs editable'),
            //    'data' => \ModUtil::getVar('CmfcmfMediaModule', 'slugEditable')
            //])
            ->add('soundCloudApiKey', 'text', [
                'label' => $this->__('SoundCloud "Client ID"'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'soundCloudApiKey'),
                'attr' => [
                    'help' => $this->__('Go to http://soundcloud.com/you/apps/new and create a new application. The name doesn\'t matter. In the next screen, enter the url of your Zikula installation at "Website of your App" and leave "Redirect URI for Authentication" empty. Then save and paste the "Client ID" here.')
                ]
            ])
            // @todo Flickr currently disabled.
            //->add('flickrApiKey', 'text', [
            //    'label' => $this->__('Flickr API Client Key'),
            //    'required' => false,
            //    'empty_data' => null,
            //    'data' => \ModUtil::getVar('CmfcmfMediaModule', 'flickrApiKey'),
            //    'attr' => [
            //        'help' => $this->__('Go to https://www.flickr.com/services/apps/create/apply and create a new application. The name doesn\'t matter. Paste the "Key" here (not the "secret key").')
            //    ]
            //])
            ->add('googleApiKey', 'text', [
                'label' => $this->__('Google API Developer Key'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'googleApiKey'),
                'attr' => [
                    'help' => $this->__('Go to https://console.developers.google.com/project and create a new project. The name and id don\'t matter. Then go to "APIs and Authentication -> APIs" and enable the "YouTube Data API v3". Then go to "APIs and Authentication -> Credentials" and click "Add credentials -> API-Key -> Server-Key". Again, the name does\'t matter. Then paste the API key here.')
                ]
            ])
            ->add('twitterApiKey', 'text', [
                'label' => $this->__('Twitter API Consumer Key'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiKey'),
                'attr' => [
                    'help' => $this->__('Go to https://apps.twitter.com/ and create a new application. The name doesn\'t matter and "Callback URL" should be empty. Then go to "Keys and Access Tokens". At the bottom, click at "Create my access token".')
                ]
            ])
            ->add('twitterApiSecret', 'text', [
                'label' => $this->__('Twitter API Secret'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiSecret')
            ])
            ->add('twitterApiAccessToken', 'text', [
                'label' => $this->__('Twitter API Access Token'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessToken')
            ])
            ->add('twitterApiAccessTokenSecret', 'text', [
                'label' => $this->__('Twitter API Access Token Secret'),
                'required' => false,
                'empty_data' => null,
                'data' => \ModUtil::getVar('CmfcmfMediaModule', 'twitterApiAccessTokenSecret')
            ])
            ->add('save', 'submit', [
                'label' => $this->__('Save'),
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
        ;
    }

    public function getName()
    {
        return 'cmfcmfmediamodule_settingstype';
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     *
     * @return string
     */
    public function __($msg)
    {
        return __($msg, $this->domain);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param int    $n  Count.
     *
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
        return _n($m1, $m2, $n, $this->domain);
    }

    /**
     * Format translations for modules.
     *
     * @param string       $msg   Message.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function __f($msg, $param)
    {
        return __f($msg, $param, $this->domain);
    }

    /**
     * Format pural translations for modules.
     *
     * @param string       $m1    Singular.
     * @param string       $m2    Plural.
     * @param int          $n     Count.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function _fn($m1, $m2, $n, $param)
    {
        return _fn($m1, $m2, $n, $param, $this->domain);
    }
}
