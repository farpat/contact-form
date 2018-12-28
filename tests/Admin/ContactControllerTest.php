<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ContactControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @throws \Exception
     */
    protected function setUp ()
    {
        parent::setUp();

        $this->client = static::createClient();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $application->run(new StringInput('doctrine:schema:update --force'));
        $application->run(new StringInput('doctrine:fixtures:load --no-interaction'));
    }

    /** @test */
    public function get_index_redirect_if_not_authentified ()
    {
        $this->client->request('GET', $this->getService('router')->generate('admin.contact.index'));

        $this->assertTrue($this->client->getResponse()->isRedirect('/login'));
    }

    /** @test */
    public function get_index ()
    {
        $userRepository = $this->getService(UserRepository::class);
        $userToConnect = $userRepository->find(1);
        $this->logIn($userToConnect);

        $contactCount = $this->getService(ContactRepository::class)->count([]);

        $crawler = $this->client->request('GET', $this->getService('router')->generate('admin.contact.index'));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount($contactCount, $crawler->filter('#contact-table tbody tr'));
    }

    /** @test */
    public function treat_contact ()
    {
        $userRepository = $this->getService(UserRepository::class);
        $userToConnect = $userRepository->find(1);
        $this->logIn($userToConnect);

        /** @var ContactRepository $contactRepository */
        $contactRepository = $this->getService(ContactRepository::class);
        $contactTreatedCount = $contactRepository
            ->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.treated_at is not null')
            ->getQuery()
            ->getSingleScalarResult();

        $contactToTreat = $contactRepository->findOneBy(['treated_at' => null]);

        $token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('admin.contact.treat.' . $contactToTreat->getId());

        /** @var Router $router */
        $router = $this->getService('router');
        $this->client->request('PATCH',
            $router->generate('admin.contact.treat', ['contact' => $contactToTreat->getId()]),
            ['_token' => $token]
        );

        $this->assertEquals($contactTreatedCount + 1, $contactRepository
            ->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.treated_at is not null')
            ->getQuery()
            ->getSingleScalarResult());
    }

    private function getService (string $service)
    {
        return self::$container->get($service);
    }

    private function logIn (User $user)
    {
        $session = self::$container->get('session');

        $firewall = 'main';

        $token = new UsernamePasswordToken($user, null, $firewall, ['ROLE_ADMIN']);
        $session->set('_security_' . $firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
