<?php

namespace Cmfcmf\Module\MediaModule\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractControllerTestCase extends WebTestCase
{
    public function setUp()
    {
        require __DIR__ . '/../../vendor/autoload.php';
    }

    /**
     * {@inheritdoc}
     */
    protected static function createClient(array $options = [], array $server = [])
    {
        $server = array_merge($server, [
            'DOCUMENT_ROOT' => getcwd(),
            'SCRIPT_NAME' => '/core/src/index.php'
        ]);

        $client = parent::createClient($options, $server);

        return $client;
    }

    public function tearDown()
    {
        static::bootKernel();
        $em = static::$kernel->getContainer()->get('doctrine.entitymanager');

        $entities = [
            'Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity'
        ];
        foreach ($entities as $className) {
            $cmd = $em->getClassMetadata($className);
            $connection = $em->getConnection();
            $dbPlatform = $connection->getDatabasePlatform();
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            $connection->executeUpdate($q);
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
