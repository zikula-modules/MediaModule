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
use Cmfcmf\Module\MediaModule\Entity\Media\SoundCloudEntity;

class SoundCloud extends AbstractMediaType implements WebMediaTypeInterface, PasteMediaTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->translator->trans('SoundCloud', [], 'cmfcmfmediamodule');
    }

    public function isEnabled()
    {
        return '' != $this->variableApi->get('CmfcmfMediaModule', 'soundCloudApiKey', '');
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'fa-soundcloud';
    }

    /**
     * {@inheritdoc}
     */
    public function matchesPaste($pastedText)
    {
        return false !== $this->getTrackFromPastedText($pastedText) ? 10 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFromPaste($pastedText)
    {
        $trackId = $this->getTrackFromPastedText($pastedText);
        if (false === $trackId) {
            throw new \RuntimeException();
        }

        $entity = new SoundCloudEntity($this->requestStack, $this->dataDirectory);
        $entity->setUrl('https://soundcloud.com/');
        $entity->setMusicId($trackId);

        return $entity;
    }

    private function getTrackFromPastedText($pastedText)
    {
        $regex = '#src=(?:"|\')((?:.*?)w\.soundcloud\.com/player/\?url\=(?:.*?))(?:"|\')#';
        preg_match($regex, $pastedText, $matches);
        if (count($matches) < 2) {
            return false;
        }
        $url = htmlspecialchars_decode($matches[1]);
        parse_str(parse_url($url, PHP_URL_QUERY), $parameters);

        if (!isset($parameters['url'])) {
            return false;
        }
        $url = $parameters['url'];

        $url = explode('/', $url);
        $trackId = $url[count($url) - 1];

        return $trackId;
    }

    public function getWebCreationTemplateArguments()
    {
        return [
            'clientId' => $this->variableApi->get('CmfcmfMediaModule', 'soundCloudApiKey')
        ];
    }

    /**
     * @param SoundCloudEntity $entity
     *
     * @return string
     */
    public function renderFullpage(AbstractMediaEntity $entity)
    {
        $url = urlencode($entity->getUrl());
        $url = <<<EOD
https://w.soundcloud.com/player/?url=$url&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true
EOD;
        $code = <<<EOD
<iframe scrolling="no" frameborder="0" allowTransparency="true" src="$url" width="100%" height="166"></iframe>
EOD;

        return $code;
    }

    public function getThumbnail(AbstractMediaEntity $entity, $width, $height, $format = 'html', $mode = 'outbound')
    {
        return false;
    }

    public function getSearchResults($q, $dropdownValue = null)
    {
        // TODO: Implement getSearchResults() method.
    }
}
