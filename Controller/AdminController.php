<?php

namespace Cmfcmf\Module\MediaModule\Controller;

class AdminController extends \Zikula_AbstractController
{
    /**
     * @todo Remove this. This is just here for legacy reasons so that a link from the extensions
     * list is provided.
     */
    public function indexAction()
    {
        $url = $this->get('router')->generate('cmfcmfmediamodule_settings_settings');
        $this->redirect($url);
    }
}
