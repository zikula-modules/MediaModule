<?php

namespace Cmfcmf\Module\MediaModule\Importer;

use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractImporter implements ImporterInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var
     */
    protected $domain;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var MediaTypeCollection
     */
    protected $mediaTypeCollection;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(TranslatorInterface $translator, FormFactory $formFactory, MediaTypeCollection $mediaTypeCollection, EntityManagerInterface $em, $domain)
    {
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->domain = $domain;
        $this->mediaTypeCollection = $mediaTypeCollection;
        $this->em = $em;
    }

    public function getId()
    {
        $type = $this->getType();
        $type = strtolower($type);

        return "cmfcmfmediamodule:$type";
    }

    public function getSettingsForm()
    {
        $form = 'Cmfcmf\\Module\\MediaModule\\Form\\Importer\\' . $this->getType() . 'Type';

        return new $form($this->translator);
    }

    /**
     * @return string
     */
    private function getType()
    {
        $type = get_class($this);
        $type = substr($type, strrpos($type, '\\') + 1, -strlen('Importer'));

        return $type;
    }
}
