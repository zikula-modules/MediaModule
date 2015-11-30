<?php

namespace Cmfcmf\Module\MediaModule\Upgrade;

use Github\Client as GitHubClient;
use Github\Exception\RuntimeException;
use Github\HttpClient\CachedHttpClient as GitHubCachedHttpClient;
use Github\HttpClient\Message\ResponseMediator as GitHubResponseMediator;
use Github\ResultPager as GitHubResultPager;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;

class VersionChecker
{
    const GITHUB_USER = 'cmfcmf';

    const GITHUB_REPO = 'MediaModule';

    private $allowedAssetContentTypes = ['application/x-zip', 'application/zip'];

    private $githubApiCache;

    /**
     * @param $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->githubApiCache = $cacheDir . '/CmfcmfMediaModule/GitHubApiCache';
    }

    /**
     * @param $currentVersion
     *
     * @return array|bool
     */
    public function getReleaseToUpgradeTo($currentVersion)
    {
        $currentVersion = new version($currentVersion);

        $releases = $this->getAllReleases();
        $releases = $this->filterPreReleasesAndDrafts($releases);
        $releases = $this->filterReleasesWithoutZipAsset($releases);

        if (count($releases) == 0) {
            return false;
        }

        // Sort by highest / biggest release first.
        usort($releases, function ($a, $b) {
            return version::rcompare($a['tag_name'], $b['tag_name']);
        });

        // Now we need to check whether we already installed the biggest available patch update.
        // I.e. if C1.C2.C3 is the currently installed version, check if there is a version C1.C2.X where X
        // is bigger than C3. If so, always update to this version first, regardless of higher available
        // versions.

        $highestPatchRelease = $this->getHighestPatchRelease($currentVersion, $releases);
        if ($highestPatchRelease === false) {
            // Somehow not even the currently installed version could be found in the GitHub releases.
            return false;
        }

        if (!version::eq($this->getVersionFromRelease($highestPatchRelease), $currentVersion)) {
            // The currently installed version is not equal to the highest patch version.
            // So upgrade to this one first.
            return $highestPatchRelease;
        }

        // If we reach this point, we already have the highest patch version installed.
        // Search for the highest C1.(C2+1).X version then.
        $highestPatchReleaseOfNextMinorVersion = $this->getHighestPatchReleaseOfNextMinorVersion(
            $currentVersion, $releases
        );
        if ($highestPatchReleaseOfNextMinorVersion === false) {
            // No new version available apparently.
            return false;
        }

        return $highestPatchReleaseOfNextMinorVersion;
    }

    /**
     * @param version $currentVersion
     * @param $releases
     *
     * @return bool|array
     */
    private function getHighestPatchRelease(version $currentVersion, $releases)
    {
        $biggestPatchUpdate = new expression(
            $currentVersion->getMajor() . "." . $currentVersion->getMinor() . ".*"
        );

        foreach ($releases as $release) {
            if ($biggestPatchUpdate->satisfiedBy($this->getVersionFromRelease($release))) {
                return $release;
            }
        }

        return false;
    }

    /**
     * @param version $currentVersion
     * @param $releases
     *
     * @return bool|array
     */
    private function getHighestPatchReleaseOfNextMinorVersion(version $currentVersion, $releases)
    {
        $nextMinorStep = new expression(
            $currentVersion->getMajor() . "." . ($currentVersion->getMinor() + 1) . ".*"
        );

        foreach ($releases as $release) {
            if ($nextMinorStep->satisfiedBy($this->getVersionFromRelease($release))) {
                return $release;
            }
        }

        return false;
    }

    /**
     * @param $releases
     *
     * @return array
     */
    private function filterPreReleasesAndDrafts($releases)
    {
        return array_filter($releases, function ($release) {
            return !$release['draft'] && !$release['prerelease'];
        });
    }

    /**
     * @param $releases
     *
     * @return array
     */
    private function filterReleasesWithoutZipAsset($releases)
    {
        return array_filter($releases, function ($release) {
            if (count($release['assets']) == 0) {
                return false;
            }
            foreach ($release['assets'] as $asset) {
                if (in_array($asset['content_type'], $this->allowedAssetContentTypes, true)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @return array
     */
    private function getAllReleases()
    {
        $client = $this->getClient();
        $paginator = new GitHubResultPager($client);

        return $paginator->fetchAll($client->repo()->releases(), 'all', [self::GITHUB_USER, self::GITHUB_REPO]);
    }

    /**
     * Checks whether or not the remaining rate limit is high enough.
     *
     * @return bool
     *
     * @throws RuntimeException if something goes wrong with the API call.
     */
    public function checkRateLimit()
    {
        $response = $this->getClient()->getHttpClient()->get('rate_limit');
        $limit    = GitHubResponseMediator::getContent($response);

        return $limit['resources']['core']['remaining'] > 10;
    }

    private function getClient()
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $client = new GitHubClient(
            new GitHubCachedHttpClient(['cache_dir' => $this->githubApiCache])
        );

        return $client;
    }

    /**
     * @param array $release
     *
     * @return version
     */
    private function getVersionFromRelease($release)
    {
        return new version($release['tag_name']);
    }
}
