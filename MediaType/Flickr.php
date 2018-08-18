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
use Cmfcmf\Module\MediaModule\Entity\Media\FlickrEntity;
use Symfony\Component\HttpFoundation\Request;

class Flickr extends AbstractMediaType implements WebMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('Flickr', [], 'cmfcmfmediamodule');
    }

    public function isEnabled()
    {
        return $this->variableApi->get('CmfcmfMediaModule', 'flickrApiKey', '') != "";
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-flickr';
    }

    public function renderFullpage(AbstractMediaEntity $entity)
    {
        /** @var FlickrEntity $entity */
        $title = htmlentities($entity->getTitle());
        $url = 'https://farm' . $entity->getFlickrFarm() . '.staticflickr.com/' . $entity->getFlickrServer() . '/' . $entity->getFlickrId() . '_' . $entity->getFlickrSecret() . '_b.jpg';
        $url = htmlentities($url);

        return <<<EOD
<img src="$url" class="img-responsive" alt="$title" />
EOD;
    }

    protected function getWebCreationTemplateArguments()
    {
        return [
            'flickrId' => $this->variableApi->get('CmfcmfMediaModule', 'flickrApiKey')
        ];
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound')
    {
        // @todo fix this.
        return false;
    }

    public function getSearchResults(Request $request, $q, $dropdownValue = null)
    {
        // TODO: Implement getSearchResults() method.
    }
}
