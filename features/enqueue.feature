Feature: Enqueue
  Background:
    Given a context file "features/bootstrap/FeatureContext.php" containing:
        """
        <?php
        use Behat\Behat\Context\Context;
        use Behat\Gherkin\Node\PyStringNode;
        use Flaconi\Behat\Context\EnqueueContext;
        use Behat\Behat\Hook\Scope\BeforeScenarioScope;
        class FeatureContext implements Context
        {
            /**
             * @var EnqueueContext
             */
            private $enqueueContext;


            /**
             * @BeforeScenario
             *
             * @param BeforeScenarioScope $scope
             */
            public function gatherContexts(BeforeScenarioScope $scope): void
            {
                /** @var InitializedContextEnvironment $environment */
                $environment = $scope->getEnvironment();

                $this->enqueueContext = $environment->getContext(EnqueueContext::class);
            }

            /**
             * @Given I push to :topic a dummy message
             */
            public function iPushToATopicADummyMessage(string $topic): void
            {
                $this->enqueueContext->haveAMessageInContextWithHeaderAndValue('bar', 'foo');
                $this->enqueueContext->pushToTopicInContextAMessage($topic, 'dummy', new PyStringNode(['{"foo": "bar"}'], 1));
            }

            /**
             * @Then the topic :topic should have a dummy message
             */
            public function topicShouldHaveADummyMessage(string $topic): void
            {
                $this->enqueueContext->topicInContextShouldHaveAMessage($topic, 'dummy', new PyStringNode(['"properties":{"foo":"bar"},"headers":{"bar":"foo"}'], 1));
            }

            /**
             * @Then the topic :topic should not have a dummy message
             */
            public function topicShouldNotHaveADummyMessage(string $topic): void
            {
                $this->enqueueContext->topicInContextShouldHaveAMessage($topic, 'dummy', new PyStringNode(['"other":{"foo":"bar"},"headers":{"bar":"foo"}'], 1));
            }
        }
        """
    And there is a phpunit config file
    And a config file "features/bootstrap/config/services.php" containing:
        """
        <?php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('contextArray', ['dummy' => new Definition(\Enqueue\Fs\FsContext::class, ['%paths.base%/enqueue', 1, 0777, 100])]);
        """
    And a Behat configuration containing:
        """
        default:
            suites:
              default:
                local_coverage_enabled: true
                contexts:
                  - FeatureContext
                  - flaconi:enqueue:
                      - '%contextArray%'
            extensions:
                BehatLocalCodeCoverage\LocalCodeCoverageExtension:
                  target_directory: '%paths.base_org%/var/coverage'
                  split_by: feature
                Flaconi\Behat\Tests\ServiceContainerExtension: ~
                Flaconi\Behat\Extension: ~
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
            When I push to order_test a dummy message
            Then the topic order_test should have a dummy message
    """
    When I run Behat
    Then it should pass with:
        """
        1 scenario (1 passed)
        2 steps (2 passed)
        """
  Scenario: push a message to a topic and check count in non existing context
    Given a feature file containing:
    """
    Feature: Passing feature
        Scenario: Passing scenario
            When I push to order_test a dummy message
            Then the count of topic order_test in context dummy2 should be 1
    """
    When I run Behat
    Then it should fail with:
        """
        1 scenario (1 failed)
        2 steps (1 passed, 1 failed)
        """

  Scenario: push a message to a topic and check content of the message and fail
    Given a feature file containing:
    """
    Feature: Passing feature
        Scenario: Passing scenario
            When I push to order_test a dummy message
            Then the topic order_test should not have a dummy message
    """
    When I run Behat
    Then it should fail with:
        """
        1 scenario (1 failed)
        2 steps (1 passed, 1 failed)
        """

  Scenario: push a message to a topic and count
    Given a feature file containing:
    """
    Feature: Passing feature
        Scenario: Passing scenario
            When I push to order_test a dummy message
            Then the count of topic order_test in context dummy should be 1
    """
    When I run Behat
    Then it should pass with:
        """
        1 scenario (1 passed)
        2 steps (2 passed)
        """

  Scenario: purge a topic
    Given a feature file containing:
    """
    Feature: Passing feature
        Scenario: Passing scenario
            Given I push to order_test a dummy message
            And the count of topic order_test in context dummy should be 1
            When I purge topic order_test
            Then the count of topic order_test in context dummy should be 0
    """
    When I run Behat
    Then it should pass with:
        """
        1 scenario (1 passed)
        4 steps (4 passed)
        """

  Scenario: count a topic for a context which is not supported
    Given a config file "features/bootstrap/config/services.php" containing:
        """
        <?php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('contextArray', ['dummy' => new Definition(\Enqueue\Null\NullContext::class)]);
        """
    And a feature file containing:
    """
    Feature: Failing feature
        Scenario: Failing scenario
            When I push to order_test a dummy message
            Then the count of topic order_test in context dummy should be 1
    """
    When I run Behat
    Then it should fail with:
        """
        Topic count is only implemented for now with support for the package "enqueue/fs"
        """

  Scenario: check a message in a topic for a context which is not supported
    Given a config file "features/bootstrap/config/services.php" containing:
        """
        <?php
        use Symfony\Component\DependencyInjection\Definition;

        $container->setParameter('contextArray', ['dummy' => new Definition(\Enqueue\Null\NullContext::class)]);
        """
    And a feature file containing:
    """
    Feature: Failing feature
        Scenario: Failing scenario
            When I push to order_test a dummy message
            Then the topic order_test should have a dummy message
    """
    When I run Behat
    Then it should fail with:
        """
        Topic count is only implemented for now with support for the package "enqueue/fs"
        """