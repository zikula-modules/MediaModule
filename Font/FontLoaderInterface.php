<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Font;

/**
 * A font loader is responsible to load fonts and return a list of FontInterfaces.
 */
interface FontLoaderInterface
{
    /**
     * @return FontInterface[]
     */
    public function loadFonts();
}
