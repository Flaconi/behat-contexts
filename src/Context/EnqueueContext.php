<?php declare(strict_types = 1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Enqueue\Fs\FsDestination;
use Exception;
use Interop\Queue\Context as QueueContext;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Webmozart\Assert\Assert;
use function explode;
use function file_get_contents;
use function json_decode;
use function substr_count;
use function trim;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
final class EnqueueContext implements Context
{
    /**
     * @var QueueContext
     */
    private $context;

    /**
     * @param QueueContext $context
     */
    public function __construct(QueueContext $context)
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
        $this->context->purgeQueue($this->context->createQueue($topicName));
    }

    /**
     * @param string       $topicName
     * @param PyStringNode $message
     *
     * @throws Exception
     *
     * @Given I push to :topic a message:
     */
    public function pushMessageToTopic(string $topicName, PyStringNode $message): void
    {
        $psrMessage = $this->context->createMessage('', json_decode($message->getRaw(), true));
        $topic = $this->context->createQueue($topicName);
        $this->context->createProducer()->send($topic, $psrMessage);
    }

    /**
     * @param string $topicName
     * @param int    $count
     *
     * @Given the count of topic :topic should be :count
     */
    public function topicCountShouldBe(string $topicName, int $count): void
    {
        $topic = $this->context->createQueue($topicName);

        if (!$topic instanceof FsDestination) {
            throw new Exception('Topic count is only implemented for now with support for the package "enqueue/fs".');
        }

        $actualCount = substr_count($this->getFileContent($topic), '|{');

        Assert::eq($actualCount, $count);
    }

    /**
     * @param string       $topicName
     * @param PyStringNode $message
     *
     * @Given the topic :topicName should have a message:
     */
    public function topicShouldHaveAMessage(string $topicName, PyStringNode $message): void
    {
        $topic = $this->context->createQueue($topicName);

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
}
