<?php

declare(strict_types=1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use Exception;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Throwable;

final class CommandContext implements Context
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Throwable */
    private $exception;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @throws Exception
     *
     * @Given I run command :commandString
     */
    public function runCommand(string $commandString) : void
    {
        $application = new Application($this->kernel);
        $argvInput   = new StringInput($commandString);
        $argvInput->setInteractive(false);
        $command = $application->get($argvInput->getFirstArgument());

        try {
            $command->run($argvInput, new BufferedOutput());
        } catch (Throwable $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @throws Exception
     *
     * @Given I run command :commandString without fail
     */
    public function runCommandWithoutFail(string $commandString) : void
    {
        $this->runCommand($commandString);
        $this->shouldNotFailWithAnException();
    }

    /**
     * @Then the command should not fail with an exception
     */
    public function shouldNotFailWithAnException() : void
    {
        Assert::assertNull($this->exception);
    }

    /**
     * @Then the command should fail with an exception
     */
    public function shouldFailWithAnException() : void
    {
        Assert::assertInstanceOf(Throwable::class, $this->exception);
    }

    /**
     * @Then the exception message is :messsage
     */
    public function excepetionMessageIs(string $message) : void
    {
        Assert::assertEquals($message, $this->exception->getMessage());
    }
}
