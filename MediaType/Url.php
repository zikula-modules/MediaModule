<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Entity\Media\UrlEntity;

class Url extends AbstractMediaType implements PasteMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Url', [], $this->domain);
    }

    public function isEnabled()
    {
        return true;
    }

    public function getIcon()
    {
        return 'fa-external-link';
    }

    /**
     * {@inheritdoc}
     */
    public function matchesPaste($pastedText)
    {
        return filter_var($pastedText, FILTER_VALIDATE_URL) !== false ? 1 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFromPaste($pastedText)
    {
        $entity = new UrlEntity();
        $entity->setUrl($pastedText);

        return $entity;
    }

    /**
     * @param AbstractMediaEntity $entity
     *
     * @return string
     */
    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var UrlEntity $entity */
        return '<a href="' . htmlspecialchars($entity->getUrl()) . '">' . htmlspecialchars($entity->getTitle()) . '</a>';
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound')
    {
        return false;
    }

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full')
    {
        return $this->renderFullpage($entity);
    }
}