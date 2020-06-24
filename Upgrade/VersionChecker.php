<?php

declare(strict_types=1);

/*
 * This file is part of the MediaModule for Zikula.
 *
 * (c) Christian Flach <hi@christianflach.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cmfcmf\Module\MediaModule\Upgrade;

use Github\Client as GitHubClient;
use Github\Exception\RuntimeException;
use Github\HttpClient\Message\ResponseMediator as GitHubResponseMediator;
use Github\ResultPager as GitHubResultPager;
use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\version;

/**
 * Checks whether or not a new version is available.
 */
class VersionChecker
{
    const GITHUB_USER = 'zikula-modules';

    const GITHUB_REPO = 'MediaModule';

    private $allowedAssetContentTypes = [
        'application/x-zip',
        'application/zip',
        'application/x-zip-compressed'
    ];

    private $githubApiCache;

    /**
     * @param $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->githubApiCache = $cacheDir . '/CmfcmfMediaModule/GitHubApiCache';
    }

    /**
     * Returns the GitHub release array to upgrade to or false if there is no such release.
     *
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

        if (0 === count($releases)) {
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
        if (false === $highestPatchRelease) {
            // Somehow not even the currently installed version could be found in the GitHub releases.
            return false;
        }

        if (version::gt($this->getVersionFromRelease($highestPatchRelease), $currentVersion)) {
            // The currently installed version is lower than the highest patch version.
            // So upgrade to this one first.
            return $highestPatchRelease;
        }

        // If we reach this point, we already have the highest patch version installed.
        // Search for the highest C1.(C2+1).X version then.
        $highestPatchReleaseOfNextMinorVersion = $this->getHighestPatchReleaseOfNextMinorVersion(
            $currentVersion, $releases
        );
        if (false === $highestPatchReleaseOfNextMinorVersion) {
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
            $currentVersion->getMajor() . '.' . $currentVersion->getMinor() . '.*'
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
            $currentVersion->getMajor() . '.' . ($currentVersion->getMinor() + 1) . '.*'
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
            return false !== $this->getZipAssetFromRelease($release);
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
     * @throws RuntimeException if something goes wrong with the API call
     */
    public function checkRateLimit()
    {
        $response = $this->getClient()->getHttpClient()->get('rate_limit');
        $limit = GitHubResponseMediator::getContent($response);

        return $limit['resources']['core']['remaining'] > 10;
    }

    /**
     * Returns the first ZIP asset of the given GitHub release or false if none is found.
     *
     * @param array $release
     *
     * @return bool
     */
    public function getZipAssetFromRelease($release)
    {
        if (0 === count($release['assets'])) {
            return false;
        }
        foreach ($release['assets'] as $asset) {
            if (in_array($asset['content_type'], $this->allowedAssetContentTypes, true)) {
                return $asset;
            }
        }

        return false;
    }

    private function getClient()
    {
        $client = new GitHubClient(
//            new GitHubCachedHttpClient(['cache_dir' => $this->githubApiCache])
        );
        // @see https://github.com/KnpLabs/php-github-api/blob/master/doc/caching.md
//        $client->addCache( );

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
