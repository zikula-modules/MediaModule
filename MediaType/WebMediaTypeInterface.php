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

use Symfony\Component\HttpFoundation\Request;

interface WebMediaTypeInterface
{
    public function getEntityFromWeb(Request $request);

    public function getSearchResults(Request $request, $q, $dropdownValue = null);
}
