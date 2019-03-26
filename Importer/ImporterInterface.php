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

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

interface ImporterInterface
{
    /**
     * The unique importer id. Must be unique across all bundles. It is recommended to prefix it with the bundle name.
     *
     * @return string
     */
    public function getId();

    /**
     * The title of the importer.
     *
     * @return string
     */
    public function getTitle();

    /**
     * The description of the importer.
     *
     * @return string
     */
    public function getDescription();

    /**
     * All restrictions of the importer, i.e. things that can't be imported.
     *
     * @return string|null
     */
    public function getRestrictions();

    /**
     * Checks whether the importer can be used. True if it can, an error message otherwise.
     *
     * @return bool|string
     */
    public function checkRequirements();

    /**
     * @return FormTypeInterface
     */
    public function getSettingsForm();

    public function import($formData, FlashBagInterface $flashBag);
}
