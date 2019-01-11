<?php declare(strict_types = 1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
class CommandContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Throwable
     */
    private $exception;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given I run command :commandString
     *
     * @param string $commandString
     *
     * @throws \Exception
     */
    public function runCommend(string $commandString): void
    {
        $application = new Application($this->kernel);
        $argvInput = new StringInput($commandString);
        $argvInput->setInteractive(false);
        $command = $application->get($argvInput->getFirstArgument());
        $stream = \fopen('php://memory', 'w+b', false);
        if (!$stream) {
            throw new \RuntimeException('stream could not be created.');
        }
        try {
            $command->run($argvInput, new StreamOutput($stream));
        } catch (Throwable $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @Given I run command :commandString without fail
     *
     * @param string $commandString
     *
     * @throws \Exception
     */
    public function runCommendWithoutFail(string $commandString): void
    {
        $this->runCommend($commandString);
        $this->shouldNotFailWithAnException();
    }

    /**
     * @Then the command should not fail with an exception
     */
    public function shouldNotFailWithAnException(): void
    {
        Assert::null($this->exception);
    }

    /**
     * @Then the command should fail with an exception
     */
    public function shouldFailWithAnException(): void
    {
        Assert::isInstanceOf($this->exception, Throwable::class);
    }

    /**
     * @Then the exception message is :messsage
     */
    public function excepetionMessageIs(string $message): void
    {
        Assert::eq($message, $this->exception->getMessage());
    }
}