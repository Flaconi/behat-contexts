<?php declare(strict_types = 1);

namespace Flaconi\Behat\Context;

use Behat\Behat\Context\Context;
use DOMDocument;
use Exception;
use GuzzleHttp\Psr7\Response;
use Http\HttplugBundle\ClientFactory\MockFactory;
use Http\Mock\Client;
use Webmozart\Assert\Assert;
use function count;
use function file_get_contents;
use function GuzzleHttp\Psr7\stream_for;

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

    public function __construct(MockFactory $factory, string $fixtureDir)
    {
        $this->client = $factory->createClient();
        $this->fixtureDir = $fixtureDir;
    }

    /**
     * @Then request to http client should be equal with :file
     *
     * @param string $file
     */
    public function requestShouldBeEqual(string $file): void
    {
        $request = $this->client->getLastRequest();

        $doc = new DOMDocument();
        $doc->loadXML((string) $request->getBody());
        $domNode = $doc->getElementsByTagName('xmlDoc')[0];

        \PHPUnit\Framework\Assert::assertXmlStringEqualsXmlString(
            file_get_contents($this->fixtureDir.'/'.$file),
            $domNode->textContent
        );
    }

    /**
     * @param string $file
     *
     * @Given the http client should respond with message from file :file
     */
    public function shouldRespondWithMessageFromFile(string $file): void
    {
        $response = new Response();

        $this->client->addResponse(
            $response->withBody(stream_for(file_get_contents($this->fixtureDir.'/'.$file)))
        );
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
}
