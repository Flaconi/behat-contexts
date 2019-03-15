<?php declare(strict_types = 1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Enqueue\Fs\FsDestination;
use Exception;
use Interop\Queue\Context as QueueContext;
use Interop\Queue\InvalidDestinationException;
use Interop\Queue\InvalidMessageException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Webmozart\Assert\Assert;
use function array_key_exists;
use function explode;
use function file_get_contents;
use function json_decode;
use function sprintf;
use function substr_count;
use function trim;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
final class EnqueueContext implements Context
{
    /**
     * @var array<QueueContext>
     */
    private $context;

    /**
     * @param array<QueueContext> $context
     */
    public function __construct(array $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $topicName
     *
     * @Given I purge topic :topic
     */
    public function purgeTopic(string $topicName): void
    {
        foreach ($this->context as $context) {
            $context->purgeQueue($context->createQueue($topicName));
        }
    }

    /**
     * @param string       $topicName
     * @param string       $contextName
     * @param PyStringNode $message
     *
     * @throws \Interop\Queue\Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     *
     * @Given I push to :topic in context :contextName a message:
     */
    public function pushMessageToTopicInContext(string $topicName, string $contextName, PyStringNode $message): void
    {
        $context = $this->getContext($contextName);

        $psrMessage = $context->createMessage('', json_decode($message->getRaw(), true));
        $topic = $context->createQueue($topicName);
        $context->createProducer()->send($topic, $psrMessage);
    }

    /**
     * @param string $topicName
     * @param string $contextName
     * @param int    $count
     *
     * @throws Exception
     *
     * @Given the count of topic :topic in context :contextName should be :count
     */
    public function topicCountInContextShouldBe(string $topicName, string $contextName, int $count): void
    {
        $context = $this->getContext($contextName);

        $topic = $context->createQueue($topicName);

        if (!$topic instanceof FsDestination) {
            throw new Exception('Topic count is only implemented for now with support for the package "enqueue/fs".');
        }

        $actualCount = substr_count($this->getFileContent($topic), '|{');

        Assert::eq($actualCount, $count);
    }

    /**
     * @param string       $topicName
     * @param string       $contextName
     * @param PyStringNode $message
     *
     * @throws Exception
     *
     * @Given the topic :topicName in :contextName should have a message:
     */
    public function topicInContextShouldHaveAMessage(string $topicName, string $contextName, PyStringNode $message): void
    {
        $context = $this->getContext($contextName);

        $topic = $context->createQueue($topicName);

        if (!$topic instanceof FsDestination) {
            throw new Exception('Topic count is only implemented for now with support for the package "enqueue/fs".');
        }

        $data = explode('|', $this->getFileContent($topic));

        foreach ($data as $d) {
            if (trim($d) === '') {
                continue;
            }

            Assert::contains($d, $message->getRaw());
        }
    }

    /**
     * @param FsDestination $topic
     *
     * @return string
     */
    private function getFileContent(FsDestination $topic): string
    {
        $fileContents = file_get_contents($topic->getFileInfo()->getPathname());

        if ($fileContents === false) {
            throw new FileNotFoundException(null, 0, null, $topic->getFileInfo()->getPathname());
        }

        return $fileContents;
    }

    private function getContext(string $contextName): QueueContext
    {
        if (array_key_exists($contextName, $this->context)) {
            return $this->context[$contextName];
        }

        throw new Exception(sprintf('Context with name "%s" was not found.', $contextName));
    }
}
