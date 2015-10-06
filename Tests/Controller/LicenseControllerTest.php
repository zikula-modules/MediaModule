<?php

namespace Cmfcmf\Module\MediaModule\Tests\Controller;

use Nelmio\Alice\Fixtures as AliceLoader;

class LicenseControllerTest extends AbstractControllerTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/m/licenses/');
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("License list")')->count()
        );

        AliceLoader::load(__DIR__.'/license_fixtures.yml', $client->getContainer()->get('doctrine.entitymanager'));

        $client = static::createClient();
        $crawler = $client->request('GET', '/m/licenses/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("blubb")')->count()
        );
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessfull($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        return [
            ['/m/licenses/'],
            ['/m/licenses/new'],
            // @todo edit
        ];
    }
}
