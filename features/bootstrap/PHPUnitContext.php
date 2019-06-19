<?php declare(strict_types=1);

namespace Flaconi\Behat\Tests;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use function file_get_contents;
use FriendsOfBehat\TestContext\Context\TestContext;
use function str_replace;

final class PHPUnitContext implements Context
{
    /** @var TestContext */
    private $testContext;

    private $phpunitXmlConfigFile;
    /**
     * @var string
     */
    private $sourceDir;

    public function __construct(string $phpunitXmlConfigFile, string $sourceDir)
    {
        $this->phpunitXmlConfigFile = $phpunitXmlConfigFile;
        $this->sourceDir            = $sourceDir;
    }

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        $this->testContext = $environment->getContext(TestContext::class);
    }

    /**
     * @Given there is a phpunit config file
     */
    public function thereIsAPHPUnitConfigFile(): void
    {
        $this->testContext->thereIsFile(
            'phpunit.xml.dist',
            str_replace('###REPLACE_SRC###', $this->sourceDir, file_get_contents($this->phpunitXmlConfigFile))
        );
    }
}