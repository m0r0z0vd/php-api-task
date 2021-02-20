<?php

namespace App\Tests\Functional;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class ItemControllerTest extends WebTestCase
{
    private const EXISTING_USERNAME = 'john';

    /** @var KernelBrowser */
    private $client;

    /** @var ItemRepository */
    private $itemsRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->itemsRepository = static::$container->get(ItemRepository::class);
    }

    public function testCreate()
    {
        $userRepository = static::$container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['username' => self::EXISTING_USERNAME]);
        $this->client->loginUser($user);

        $data = 'very secure new item data ' . microtime(true);
        $newItemData = ['data' => $data];
        $this->client->request('POST', '/item', $newItemData);

        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertEquals([], $content);

        $lastUserItem = $this->itemsRepository->findOneBy(['user' => $user], ['id' => 'DESC']);
        $this->assertInstanceOf(Item::class, $lastUserItem);
        $this->assertEquals($data, $lastUserItem->getData());
    }
}
