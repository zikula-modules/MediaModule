<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Importer;

use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractImporter implements ImporterInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Filesystem
     */
    protected $filesystem;

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

    /**
     * @var string
     */
    protected $dataDirectory;

    /**
     * @param TranslatorInterface    $translator
     * @param Filesystem             $filesystem
     * @param FormFactory            $formFactory
     * @param MediaTypeCollection    $mediaTypeCollection
     * @param EntityManagerInterface $em
     * @param string                 $dataDirectory
     */
    public function __construct(
        TranslatorInterface $translator,
        Filesystem $filesystem,
        FormFactory $formFactory,
        MediaTypeCollection $mediaTypeCollection,
        EntityManagerInterface $em,
        $dataDirectory
    ) {
        $this->translator = $translator;
        $this->filesystem = $filesystem;
        $this->formFactory = $formFactory;
        $this->mediaTypeCollection = $mediaTypeCollection;
        $this->em = $em;
        $this->dataDirectory = $dataDirectory;
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
