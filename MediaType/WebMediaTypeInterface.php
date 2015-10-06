<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Symfony\Component\HttpFoundation\Request;

interface WebMediaTypeInterface
{
    public function getIcon();

    public function getEntityFromWeb(Request $request);

    public function getSearchResults(Request $request, $q, $dropdownValue = null);
}
