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

namespace Cmfcmf\Module\MediaModule\Importer;

use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var RequestStack
     */
    protected $requestStack;

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
     * @param RequestStack           $requestStack
     * @param string                 $dataDirectory
     */
    public function __construct(
        TranslatorInterface $translator,
        Filesystem $filesystem,
        FormFactory $formFactory,
        MediaTypeCollection $mediaTypeCollection,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        $dataDirectory
    ) {
        $this->translator = $translator;
        $this->filesystem = $filesystem;
        $this->formFactory = $formFactory;
        $this->mediaTypeCollection = $mediaTypeCollection;
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->dataDirectory = $dataDirectory;
    }

    public function getId()
    {
        $type = $this->getType();
        $type = mb_strtolower($type);

        return 'cmfcmfmediamodule:' . $type;
    }

    public function getSettingsForm()
    {
        return 'Cmfcmf\\Module\\MediaModule\\Form\\Importer\\' . $this->getType() . 'Type';
    }

    /**
     * @return string
     */
    private function getType()
    {
        $type = get_class($this);
        $type = mb_substr($type, mb_strrpos($type, '\\') + 1, -mb_strlen('Importer'));

        return $type;
    }
}
