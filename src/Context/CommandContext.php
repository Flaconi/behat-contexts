<?php declare(strict_types = 1);

namespace App\Behat;

use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
class CommandContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When I run command :commandString
     *
     * @param string $commandString
     *
     * @throws \Exception
     */
    public function runCommendInMode(string $commandString): void
    {
        $application = new Application($this->kernel);
        $argvInput = new StringInput($commandString);
        $argvInput->setInteractive(false);
        $command = $application->get($argvInput->getFirstArgument());
        $stream = \fopen('php://memory', 'w+b', false);
        if (!$stream) {
            throw new \RuntimeException('stream could not be created.');
        }
        $command->run($argvInput, new StreamOutput($stream));
    }
}