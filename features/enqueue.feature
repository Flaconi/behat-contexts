Feature: Enqueue
  Background:
    Given a context file "features/bootstrap/FeatureContext.php" containing:
        """
        <?php
        use Behat\Behat\Context\Context;
        class FeatureContext implements Context
        {
            public function __construct(string $key) {
              $this->key = $key;
            }
            /** @Then it passes */
            public function itPasses() {}
            /** @Then it fails */
            public function itFails() { throw new \RuntimeException(); }
            /** @Then it prints env */
            public function itPassesWithOutput() { echo $this->key; }
            /** @Then it fails with output :output */
            public function itFailsWithOutput($output) { throw new \RuntimeException($output); }
        }
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

  Scenario: push a message to a topic and count
    Given a Behat configuration containing:
        """
        default:
            suites:
              default:
                contexts:
                  - flaconi:enqueue:
                      - '@context'
            extensions:
                Flaconi\Behat\Extension: ~
        """
    And a feature file "features/passing_scenario.feature" containing:
        """
        Feature: Passing feature
            Scenario: Passing scenario
                Given I push to order_test a message:

                And the count of topic order_test should be 1
        """3
    When I run Behat
    Then it should pass with "FOO"
    Then it should pass with:
        """
        1 scenario (1 passed)
        1 step (1 passed)
        """