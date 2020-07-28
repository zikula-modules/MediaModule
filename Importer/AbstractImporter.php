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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var FormFactoryInterface
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

    public function __construct(
        TranslatorInterface $translator,
        Filesystem $filesystem,
        FormFactoryInterface $formFactory,
        MediaTypeCollection $mediaTypeCollection,
        EntityManagerInterface $em,
        RequestStack $requestStack,
        string $dataDirectory
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
        $type = static::class;
        $type = mb_substr($type, mb_strrpos($type, '\\') + 1, -mb_strlen('Importer'));

        return $type;
    }
}
