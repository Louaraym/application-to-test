<?php


namespace Tests\AppBundle\Security;


use AppBundle\Entity\User;
use AppBundle\Security\GithubUserProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use JMS\Serializer\Serializer;
use GuzzleHttp\Client;

class GithubUserProviderTest extends TestCase
{
    private $client;
    private $serializer;
    private $streamedResponse;
    private $response;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->serializer = $this
            ->getMockBuilder(Serializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->streamedResponse = $this
            ->getMockBuilder(StreamInterface::class)
            ->getMock();

        $this->response = $this
            ->getMockBuilder(ResponseInterface::class)
            ->getMock();
    }

    public function testLoadUserByUsernameReturningAUser()
    {
        $this->client
            ->expects($this->once()) // Nous nous attendons à ce que la méthode get soit appelée une fois
            ->method('get')
            ->willReturn($this->response)
        ;

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamedResponse)
        ;

        $userData = [
            'login' => 'a login',
            'name' => 'user name',
            'email' => 'adress@mail.com',
            'avatar_url' => 'url to the avatar',
            'html_url' => 'url to profile'
        ];

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData)
        ;

        //Instanciation de GithubUserProvider grâce aux doublures créés
        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

        //Appel de la méthode à tester
        $user = $githubUserProvider->loadUserByUsername('an-access-token');

        $expectedUser = new User(
            $userData['login'],
            $userData['name'],
            $userData['email'],
            $userData['avatar_url'],
            $userData['html_url']
        );

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals(User::class, get_class($user));

    }

    public function testLoadUserByUsernameThrowingException()
    {
        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->response)
        ;

        $streamedResponse = $this
            ->getMockBuilder(StreamInterface::class)
            ->getMock();

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamedResponse);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([]);

        $this->expectException('LogicException');

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);
        $githubUserProvider->loadUserByUsername('an-access-token');
    }

    public function tearDown()
    {
        $this->client = null;
        $this->serializer = null;
        $this->streamedResponse = null;
        $this->response = null;
    }

}