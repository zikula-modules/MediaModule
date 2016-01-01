<?php

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
