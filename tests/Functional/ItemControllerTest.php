<?php

namespace App\Tests\Functional;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class ItemControllerTest extends WebTestCase
{
    private const EXISTING_USERNAME = 'john';

    /** @var KernelBrowser */
    private $client;

    /** @var ItemRepository */
    private $itemsRepository;

    /** @var User */
    private $existingUser;

    /** @var EntityManagerInterface */
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->itemsRepository = static::$container->get(ItemRepository::class);
        $userRepository = static::$container->get(UserRepository::class);
        $this->existingUser = $userRepository->findOneBy(['username' => self::EXISTING_USERNAME]);
        $this->client->loginUser($this->existingUser);
        $this->entityManager = self::$container->get(EntityManagerInterface::class);
    }

    public function testList(): void
    {
        $item = $this->makeItem();

        $this->client->request('GET', '/item');

        $totalForExistingUser = $this->itemsRepository->count(['user' => $this->existingUser]);

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertGreaterThan(0, count($content));
        $this->assertCount($totalForExistingUser, $content);
        $lastItemIndex = count($content) - 1;
        $this->assertArrayHasKey($lastItemIndex, $content);
        $lastItem = $content[$lastItemIndex];
        $this->assertIsArray($lastItem);
        $this->assertArrayHasKey('id', $lastItem);
        $this->assertArrayHasKey('data', $lastItem);
        $this->assertArrayHasKey('created_at', $lastItem);
        $this->assertArrayHasKey('updated_at', $lastItem);
        $this->assertEquals($item->getData(), $lastItem['data']);
        $this->assertEquals($item->getId(), $lastItem['id']);
    }

    public function testCreate(): void
    {
        $data = 'very secure new item data ' . microtime(true);
        $newItemData = ['data' => $data];
        $this->client->request('POST', '/item', $newItemData);

        $this->assertResponseIsSuccessful();
        $this->assertResponseArrayData([]);

        $lastUserItem = $this->itemsRepository->findOneBy(['user' => $this->existingUser], ['id' => 'DESC']);
        $this->assertInstanceOf(Item::class, $lastUserItem);
        $this->assertEquals($data, $lastUserItem->getData());
    }

    public function testCreateWithoutData(): void
    {
        $this->client->request('POST', '/item');
        $this->assertResponseIsSuccessful();
        $this->assertResponseArrayData([
            'error' => 'No data parameter'
        ]);
    }

    public function testCreateWithInvalidData(): void
    {
        $this->client->request('POST', '/item', ['data' => '']);
        $this->assertResponseIsSuccessful();
        $this->assertResponseArrayData([
            'error' => 'No data parameter'
        ]);
    }

    public function testUpdate(): void
    {
        $item = $this->makeItem();
        $data = 'updated data ' . microtime(true);
        $id = $item->getId();
        $content = 'Content-Disposition: form-data; name="id"' . PHP_EOL . PHP_EOL . $id . PHP_EOL;
        $content .= 'Content-Disposition: form-data; name="data"' . PHP_EOL . PHP_EOL . $data . PHP_EOL;

        $this->client->request('PUT', '/item', [], [], [], $content);

        $this->assertResponseIsSuccessful();
        $this->assertResponseArrayData([]);

        $item = $this->itemsRepository->find($id);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals($data, $item->getData());
    }

    public function testUpdateIfInvalidData(): void
    {
        $item = $this->makeItem();
        $data = '';
        $id = $item->getId();
        $content = 'Content-Disposition: form-data; name="id"' . PHP_EOL . PHP_EOL . $id . PHP_EOL;
        $content .= 'Content-Disposition: form-data; name="data"' . PHP_EOL . PHP_EOL . $data . PHP_EOL;

        $this->client->request('PUT', '/item', [], [], [], $content);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No data parameter (data)'
        ]);
    }

    public function testUpdateIfNoData(): void
    {
        $item = $this->makeItem();
        $id = $item->getId();
        $content = 'Content-Disposition: form-data; name="id"' . PHP_EOL . PHP_EOL . $id . PHP_EOL;

        $this->client->request('PUT', '/item', [], [], [], $content);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No data parameter (data)'
        ]);
    }

    public function testUpdateIfNoId(): void
    {
        $this->client->request('PUT', '/item', [], [], [], '');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No data parameter (id)'
        ]);
    }

    public function testUpdateIfEmptyId(): void
    {
        $id = '';
        $content = 'Content-Disposition: form-data; name="id"' . PHP_EOL . PHP_EOL . $id . PHP_EOL;

        $this->client->request('PUT', '/item', [], [], [], $content);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No data parameter (id)'
        ]);
    }

    public function testUpdateIfEmptyIdIsZero(): void
    {
        $id = '0';
        $content = 'Content-Disposition: form-data; name="id"' . PHP_EOL . PHP_EOL . $id . PHP_EOL;

        $this->client->request('PUT', '/item', [], [], [], $content);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No data parameter (id)'
        ]);
    }

    public function testUpdateIfEmptyIdIsWrong(): void
    {
        $id = '-1';
        $data = 'data';
        $content = 'Content-Disposition: form-data; name="id"' . PHP_EOL . PHP_EOL . $id . PHP_EOL;
        $content .= 'Content-Disposition: form-data; name="data"' . PHP_EOL . PHP_EOL . $data . PHP_EOL;

        $this->client->request('PUT', '/item', [], [], [], $content);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No item'
        ]);
    }

    public function testDelete(): void
    {
        $item = $this->makeItem();
        $id = $item->getId();

        $this->client->request('DELETE', '/item/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertResponseArrayData([]);

        $item = $this->itemsRepository->find($id);
        $this->assertNull($item);
    }

    public function testDeleteIfIdIsZero(): void
    {
        $this->client->request('DELETE', '/item/0');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No data parameter'
        ]);
    }

    public function testDeleteIfIdIsInvalid(): void
    {
        $this->client->request('DELETE', '/item/-1');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseArrayData([
            'error' => 'No item'
        ]);
    }

    /**
     * @param array $expectedData
     */
    private function assertResponseArrayData(array $expectedData): void
    {
        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);
        $content = json_decode($content, true);
        $this->assertEquals($expectedData, $content);
    }

    /**
     * @return Item
     */
    private function makeItem(): Item
    {
        $item = new Item();
        $item->setData('' . microtime(true));
        $item->setUser($this->existingUser);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }
}
