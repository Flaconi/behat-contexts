<?php declare(strict_types = 1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use DOMDocument;
use Exception;
use Http\Message\ResponseFactory;
use Http\Mock\Client;
use InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Webmozart\Assert\Assert;
use function count;
use function file_get_contents;
use function sprintf;

/**
 * @author Alexander Miehe <alexander.miehe@flaconi.de>
 */
final class HttpClientContext implements Context
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $fixtureDir;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    public function __construct(Client $client, string $fixtureDir, ResponseFactory $responseFactory)
    {
        $this->client = $client;
        $this->fixtureDir = $fixtureDir;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @Then request to http client should be equal with :file
     *
     * @param string $file
     */
    public function requestShouldBeEqual(string $file): void
    {
        $request = $this->client->getLastRequest();

        $actualDoc = new DOMDocument();
        $actualDoc->loadXML((string) $request->getBody());

        $expectedDoc = new DOMDocument();
        $expectedDoc->load($this->getFileContents($this->fixtureDir.'/'.$file));

        Assert::eq($actualDoc, $expectedDoc);
    }

    /**
     * @param string $file
     *
     * @Given the http client should respond with message from file :file
     */
    public function shouldRespondWithMessageFromFile(string $file): void
    {
        $this->shouldRespondWithStatusAndMessageFromFile(200, $file);
    }

    /**
     * @param int    $status
     * @param string $file
     *
     * @Given the http client should respond with :status and message from file :file
     */
    public function shouldRespondWithStatusAndMessageFromFile(int $status, string $file): void
    {
        $response = $this->responseFactory->createResponse(
            $status,
            null,
            [],
            $this->getFileContents($this->fixtureDir.'/'.$file)
        );

        $this->client->addResponse($response);
    }

    /**
     * @Then no request should be send
     *
     * @throws Exception
     */
    public function noRequestShouldBeSend(): void
    {
        Assert::eq(count($this->client->getRequests()), 0);
    }

    /**
     * @param int $count
     *
     * @Then request count should be :count
     *
     * @throws Exception
     */
    public function requestCountShouldBe(int $count): void
    {
        Assert::eq(count($this->client->getRequests()), $count);
    }

    /**
     * @param int    $status
     *
     * @Given the http client should respond with :status
     */
    public function shouldRespondWithStatusOnly(int $status): void
    {
        $this->client->addResponse($this->responseFactory->createResponse($status));
    }

    /**
     * @Given the http client should have a request for :uri
     *
     * @param string $uri
     *
     * @throws InvalidArgumentException
     */
    public function shouldHaveARequestForUri(string $uri): void
    {
        foreach ($this->client->getRequests() as $request) {
            if ($request->getUri()->__toString() === $uri) {
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Uri "%s" was never called.', $uri));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getFileContents(string $path): string
    {
        $fileContents = file_get_contents($path);

        if ($fileContents === false) {
            throw new FileNotFoundException(null, 0, null, $path);
        }

        return $fileContents;
    }
}
