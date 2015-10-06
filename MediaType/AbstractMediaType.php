<?php

namespace Cmfcmf\Module\MediaModule\MediaType;

use Cmfcmf\Module\MediaModule\Entity\Media\AbstractMediaEntity;
use Cmfcmf\Module\MediaModule\Translation\TranslationTrait;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Common\I18n\TranslatableInterface;

abstract class AbstractMediaType implements MediaTypeInterface, TranslatableInterface
{
    use TranslationTrait;

    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    public function __construct(EngineInterface $renderEngine)
    {
        $this->domain = \ZLanguage::getModuleDomain('CmfcmfMediaModule');
        $this->renderEngine = $renderEngine;
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

    public function renderWebCreationTemplate()
    {
        return $this->renderEngine->render(
            'CmfcmfMediaModule:MediaType:' . ucfirst($this->getAlias()) . '/WebCreation.html.twig',
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
        $entity = new $entity();

        $settings = json_decode($request->request->get('settings'), true);
        foreach ($settings as $name => $value) {
            $setter = 'set' . ucfirst($name);
            $entity->$setter($value);
        }

        return $entity;
    }
}
