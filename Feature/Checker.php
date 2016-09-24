<?php

namespace Cmfcmf\Module\MediaModule\Feature;

use Cmfcmf\Module\MediaModule\Exception\FeatureNotFoundException;
use Cmfcmf\Module\MediaModule\Security\SecurityManager;
use Zikula\ExtensionsModule\Api\VariableApi;

class Checker
{
    /**
     * @var SecurityManager
     */
    private $securityManager;

    /**
     * @var Feature[]
     */
    private $features;

    /**
     * @var VariableApi
     */
    private $variableApi;

    public function __construct(SecurityManager $securityManager, VariableApi $variableApi)
    {
        $this->securityManager = $securityManager;
        $this->variableApi = $variableApi;

        $this->createFeatures();
    }

    public function isEnabled($featureId)
    {
        $feature = $this->findFeature($featureId);

        return $this->isFeatureEnabled($feature);
    }

    public function getFeaturesForTemplate()
    {
        return array_map(function (Feature $feature) {
            return [
                'id' => $feature->getId(),
                'description' => $feature->getDescription(),
                'enabled' => $this->isFeatureEnabled($feature)
            ];
        }, $this->features);
    }

    private function createFeatures()
    {
        $this->features[] = new Feature("collectionDescription");
        $this->features[] = new Feature("collectionCategories");
        $this->features[] = new Feature("collectionTemplate");
        $this->features[] = new Feature("collectionPrimaryMedium");
        $this->features[] = new Feature("collectionWatermarks");

        $this->features[] = new Feature("viewPermissions");
        $this->features[] = new Feature("breadcrumbs");
        $this->features[] = new Feature("searchbar");
        $this->features[] = new Feature("sortable");

        $this->features[] = new Feature("mediaCategories");
        $this->features[] = new Feature("mediaDescription");
        $this->features[] = new Feature("mediaLicense");
        $this->features[] = new Feature("mediaAuthor");
        $this->features[] = new Feature("mediaDownloadToggable");
    }

    /**
     * @param string $featureId
     *
     * @return Feature
     */
    private function findFeature($featureId)
    {
        foreach ($this->features as $feature) {
            if ($feature->getId() == $featureId) {
                return $feature;
            }
        }

        throw new FeatureNotFoundException();
    }

    /**
     * @param Feature $feature
     *
     * @return bool
     */
    private function isFeatureEnabled(Feature $feature)
    {
        return $this->securityManager->hasPermissionRaw('CmfcmfMediaModule:feature:', ":$feature:", ACCESS_OVERVIEW);
    }
}