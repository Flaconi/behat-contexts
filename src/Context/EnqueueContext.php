<?php

declare(strict_types=1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Enqueue\Fs\FsDestination;
use Exception;
use Interop\Queue\Context as QueueContext;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Message;
use PHPUnit\Framework\Assert;
use Safe\Exceptions\JsonException;
use SebastianBergmann\Exporter\Exporter;
use function array_key_exists;
use function explode;
use function mb_strpos;
use function reset;
use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\sprintf;
use function substr_count;

final class EnqueueContext implements Context
{
    /** @var array<QueueContext> */
    private $context;
    /** @var Message */
    private $message;

    /**
     * @param array<QueueContext> $context
     */
    public function __construct(array $context)
    {
        $this->context = $context;
        $this->message = reset($context)->createMessage();
    }

    /**
     * @Given I purge topic :topic
     */
    public function purgeTopic(string $topicName) : void
    {
        foreach ($this->context as $context) {
            $context->purgeQueue($context->createQueue($topicName));
        }
    }

    /**
     * @throws JsonException
     *
     * @Given I have a message with properties from json:
     */
    public function haveAMessageInContextWithPropertiesFromJson(PyStringNode $jsonProperties) : void
    {
        $this->message->setProperties(json_decode($jsonProperties->getRaw(), true));
    }

    /**
     * @throws JsonException
     *
     * @Given I have a message with header :headerName and value :headerValue
     */
    public function haveAMessageInContextWithHeaderAndValue(string $headerName, string $headerValue) : void
    {
        $this->message->setHeader($headerName, $headerValue);
    }

    /**
     * @Given I push a message in context :contextName to :topic
     */
    public function pushMessageInContextToTopic(string $topicName, string $contextName) : void
    {
        $context = $this->getContext($contextName);
        $topic   = $context->createQueue($topicName);
        $context->createProducer()->send($topic, $this->message);
    }

    /**
     * @throws \Interop\Queue\Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     *
     * @Given I push to :topic in context :contextName a message:
     */
    public function pushToTopicInContextAMessage(string $topicName, string $contextName, PyStringNode $message) : void
    {
        $this->haveAMessageInContextWithPropertiesFromJson($message);
        $this->pushMessageInContextToTopic($topicName, $contextName);
    }

    /**
     * @throws Exception
     *
     * @Given the count of topic :topic in context :contextName should be :count
     */
    public function topicCountInContextShouldBe(string $topicName, string $contextName, int $count) : void
    {
        $context = $this->getContext($contextName);

        $topic = $context->createQueue($topicName);

        if (! $topic instanceof FsDestination) {
            throw new Exception('Topic count is only implemented for now with support for the package "enqueue/fs".');
        }

        $actualCount = substr_count(file_get_contents($topic->getFileInfo()->getPathname()), '|{');

        Assert::assertEquals($actualCount, $count);
    }

    /**
     * @throws Exception
     *
     * @Given the topic :topicName in :contextName should have a message:
     */
    public function topicInContextShouldHaveAMessage(string $topicName, string $contextName, PyStringNode $message) : void
    {
        $context = $this->getContext($contextName);

        $topic = $context->createQueue($topicName);

        if (! $topic instanceof FsDestination) {
            throw new Exception('Topic count is only implemented for now with support for the package "enqueue/fs".');
        }

        $data = explode('|', file_get_contents($topic->getFileInfo()->getPathname()));

        foreach ($data as $d) {
            if (mb_strpos($d, $message->getRaw()) !== false) {
                return;
            }
        }

        throw new Exception(sprintf('Expected to find \'%s\' in \'%s\'', $message->getRaw(), (new Exporter())->export($data)));
    }

    private function getContext(string $contextName) : QueueContext
    {
        if (array_key_exists($contextName, $this->context)) {
            return $this->context[$contextName];
        }

        throw new Exception(sprintf('Context with name "%s" was not found.', $contextName));
    }
}
