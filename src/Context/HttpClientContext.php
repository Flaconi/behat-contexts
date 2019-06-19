<?php

declare(strict_types=1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Exception;
use Http\Message\ResponseFactory;
use Http\Mock\Client;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\sprintf;

final class HttpClientContext implements Context
{
    /** @var Client */
    private $client;

    /** @var string */
    private $fixtureDir;

    /** @var ResponseFactory */
    private $responseFactory;

    public function __construct(Client $client, string $fixtureDir, ResponseFactory $responseFactory)
    {
        $this->client          = $client;
        $this->fixtureDir      = $fixtureDir;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @Then request to http client should be equal with :file
     * @Then last request to http client should be equal with xml in file :file
     */
    public function requestShouldBeEqual(string $file) : void
    {
        $request = $this->client->getLastRequest();

        Assert::assertXmlStringEqualsXmlFile($this->fixtureDir . '/' . $file, (string) $request->getBody());
    }

    /**
     * @Given the http client should respond with message from file :file
     */
    public function shouldRespondWithMessageFromFile(string $file) : void
    {
        $this->shouldRespondWithStatusAndMessageFromFile(200, $file);
    }

    /**
     * @Given the http client should respond with :status and message from file :file
     */
    public function shouldRespondWithStatusAndMessageFromFile(int $status, string $file) : void
    {
        $response = $this->responseFactory->createResponse(
            $status,
            null,
            [],
            file_get_contents($this->fixtureDir . '/' . $file),
        );

        $this->client->addResponse($response);
    }

    /**
     * @throws Exception
     *
     * @Then no request should be send
     */
    public function noRequestShouldBeSend() : void
    {
        $this->requestCountShouldBe(0);
    }

    /**
     * @throws Exception
     *
     * @Then request count should be :count
     */
    public function requestCountShouldBe(int $count) : void
    {
        Assert::assertCount($count, $this->client->getRequests());
    }

    /**
     * @Given the http client should respond with :status
     */
    public function shouldRespondWithStatusOnly(int $status) : void
    {
        $this->client->addResponse($this->responseFactory->createResponse($status));
    }

    /**
     * @throws InvalidArgumentException
     *
     * @Given the http client should have a request for :uri
     */
    public function shouldHaveARequestForUri(string $uri) : void
    {
        foreach ($this->client->getRequests() as $request) {
            if ($request->getUri()->__toString() === $uri) {
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Uri "%s" was never called.', $uri));
    }

    /**
     * @throws InvalidArgumentException
     *
     * @Given the http client should have a request for :method :uri with json body:
     */
    public function shouldHaveARequestForUriWithBody(string $method, string $uri, PyStringNode $body) : void
    {
        foreach ($this->client->getRequests() as $request) {
            if ($request->getMethod() === $method && $request->getUri()->__toString() === $uri) {
                Assert::assertEquals(json_decode($body->getRaw(), true), json_decode((string) $request->getBody(), true));

                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Uri "%s" was never called.', $uri));
    }

    /**
     * @throws InvalidArgumentException
     *
     * @Given the http client should have a request for :method :uri
     */
    public function shouldHaveARequestForUriAndMethod(string $method, string $uri) : void
    {
        foreach ($this->client->getRequests() as $request) {
            if ($request->getMethod() === $method && $request->getUri()->__toString() === $uri) {
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Uri \'%s\' was never called.', $uri));
    }
}
