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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

abstract class AbstractMediaType implements MediaTypeInterface
{
    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var VariableApiInterface
     */
    protected $variableApi;

    /**
     * @var string
     */
    protected $dataDirectory;

    /**
     * @param EngineInterface      $renderEngine
     * @param TranslatorInterface  $translator
     * @param VariableApiInterface $variableApi
     * @param string               $dataDirectory
     */
    public function __construct(
        EngineInterface $renderEngine,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        $dataDirectory
    ) {
        $this->renderEngine = $renderEngine;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
        $this->dataDirectory = $dataDirectory;
    }

    public function getVar($name, $default)
    {
        return $this->variableApi->get('CmfcmfMediaModule', 'mediaType:' . $this->getAlias() . ':' . $name, $default);
    }

    public function setVar($name, $value)
    {
        $this->variableApi->set('CmfcmfMediaModule', 'mediaType:' . $this->getAlias() . ':' . $name, $value);
    }

    public function isEnabled()
    {
        return true;
    }

    public function getEntityClass()
    {
        return 'Cmfcmf\\Module\\MediaModule\\Entity\\Media\\' . ucfirst($this->getAlias()) . 'Entity';
    }

    public function getFormTypeClass()
    {
        return 'Cmfcmf\\Module\\MediaModule\\Form\\Media\\' . ucfirst($this->getAlias()) . 'Type';
    }

    public function getFormOptions(AbstractMediaEntity $entity)
    {
        return [];
    }

    public function renderWebCreationTemplate()
    {
        return $this->renderEngine->render(
            'CmfcmfMediaModule:MediaType:' . ucfirst($this->getAlias()) . '/webCreation.html.twig',
            $this->getWebCreationTemplateArguments()
        );
    }

    protected function getWebCreationTemplateArguments()
    {
        return [];
    }

    public function getAlias()
    {
        $class = get_class($this);
        $class = explode('\\', $class);
        $class = $class[count($class) - 1];

        return lcfirst($class);
    }

    public function isEmbeddable()
    {
        return true;
    }

    public function getEmbedCode(AbstractMediaEntity $entity, $size = 'full')
    {
        return $this->renderFullpage($entity) . $entity->getAttribution();
    }

    public function getExtendedMetaInformation(AbstractMediaEntity $entity)
    {
        return null;
    }

    public function toArray()
    {
        return [
            'alias' => $this->getAlias(),
            'displayName' => $this->getDisplayName(),
            'entityClass' => $this->getEntityClass()
        ];
    }

    public function doGetRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    public function doJsonGetRequest($url)
    {
        $result = $this->doGetRequest($url);
        $result = @json_decode($result, true);
        if (!is_array($result)) {
            throw new \RuntimeException();
        }

        return $result;
    }

    public function getEntityFromWeb(Request $request)
    {
        $entity = $this->getEntityClass();
        $entity = new $entity($this->dataDirectory);

        $settings = json_decode($request->request->get('settings'), true);
        foreach ($settings as $name => $value) {
            $setter = 'set' . ucfirst($name);
            $entity->$setter($value);
        }

        return $entity;
    }
}
