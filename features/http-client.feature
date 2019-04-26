Feature: Enqueue
  Background:
    Given a context file "features/bootstrap/FeatureContext.php" containing:
        """
        <?php
        use Behat\Behat\Context\Context;
        use Http\Mock\Client;
        use Http\Message\RequestFactory;
        class FeatureContext implements Context
        {
            private $client;
            private $requestFactory;

            public function __construct(Client $client, RequestFactory $requestFactory)
            {
              $this->client = $client;
              $this->requestFactory = $requestFactory;
            }

            /**
             * @Given I send a request
             */
            public function doRequest()
            {
              $this->client->doSendRequest($this->requestFactory->createRequest('GET', 'path'));
            }

        }
        """
    And there is a phpunit config file
    And a config file "features/fixtures/http/dummy.json" containing:
        """
        {
          "foo": "bar"
        }
        """
    And a config file "features/bootstrap/config/services.php" containing:
        """
        <?php
        use Symfony\Component\DependencyInjection\Definition;
        use Symfony\Component\DependencyInjection\Reference;

        $container->setDefinition('httplug.client.mock', (new Definition(\Http\Mock\Client::class, [new Reference('httplug.message_factory.default')]))->setPublic(true));
        $container->setDefinition('httplug.message_factory.default', (new Definition(\Http\Message\MessageFactory\GuzzleMessageFactory::class))->setPublic(true));
        """
    And a Behat configuration containing:
        """
        default:
            suites:
              default:
                local_coverage_enabled: true
                contexts:
                  - FeatureContext:
                      - '@httplug.client.mock'
                      - '@httplug.message_factory.default'
                  - flaconi:httpClient:
                      client: '@httplug.client.mock'
                      fixtureDir: '%paths.base%/features/fixtures/http'
                      responseFactory: '@httplug.message_factory.default'
            extensions:
                BehatLocalCodeCoverage\LocalCodeCoverageExtension:
                  target_directory: '%paths.base_org%/var/coverage'
                  split_by: feature
                Flaconi\Behat\Extension: ~
                Flaconi\Behat\Tests\ServiceContainerExtension: ~
                FriendsOfBehat\ServiceContainerExtension:
                    imports:
                        - features/bootstrap/config/services.php
        """

  Scenario: Pass Behat when no context is enabled
    Given a feature file with passing scenario
    And a Behat configuration containing:
        """
        default:
            extensions:
                Flaconi\Behat\Extension: ~
        """
    When I run Behat
    Then it should pass with:
        """
        1 scenario (1 passed)
        1 step (1 passed)
        """
  Scenario: push a message to a topic and check content of the message
    Given a feature file containing:
    """
    Feature: Passing feature
        Scenario: Passing scenario
            Given the http client should respond with message from file "dummy.json"
            When I send a request
            Then request count should be 1
    """
    When I run Behat
    Then it should pass with:
        """
        1 scenario (1 passed)
        3 steps (3 passed)
        """

  Scenario: push a message to a topic and check content of the message2
    Given a feature file containing:
    """
    Feature: Passing feature
        Scenario: Passing scenario
            Given the http client should respond with message from file "dummy.json"
            When I send a request
            When I send a request
            Then request count should be 2
    """
    When I run Behat
    Then it should pass with:
        """
        1 scenario (1 passed)
        4 steps (4 passed)
        """

