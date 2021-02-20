<?php

namespace App\Tests\Unit\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Service\ItemService;
use DateTime;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ItemServiceTest extends TestCase
{
    /** @var ItemService */
    private $itemService;

    /** @var Item|null */
    private $persistedItem = null;

    /** @var bool */
    private $flushWasCalled = false;

    /** @var bool */
    private $removeWasCalled = false;

    protected function setUp(): void
    {
        $entityManager = $this->mockEntityManager();
        $this->itemService = new ItemService($entityManager);
    }

    public function testToArrayData(): void
    {
        $date = new DateTime();
        $items = [
            (new Item())->setData('1')->setCreatedAt($date)->setUpdatedAt($date),
            (new Item())->setData('2')->setCreatedAt($date)->setUpdatedAt($date),
        ];

        $arrayData = $this->itemService->toArrayData($items);

        $expectedData = [
            [
                'id' => null,
                'data' => '1',
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'id' => null,
                'data' => '2',
                'created_at' => $date,
                'updated_at' => $date,
            ]
        ];
        $this->assertEquals($expectedData, $arrayData);
    }

    public function testCreate(): void
    {
        $user = new User();
        $user->setUsername('user');
        $data = 'secret data';

        $this->itemService->create($user, $data);
        $this->assertInstanceOf(Item::class, $this->persistedItem);
        $this->assertTrue($this->flushWasCalled);
        $this->assertEquals($user, $this->persistedItem->getUser());
        $this->assertEquals($data, $this->persistedItem->getData());
    }

    public function testUpdate(): void
    {
        $item = new Item();
        $item->setData('data');
        $data = 'new data';

        $this->itemService->update($item, $data);
        $this->assertInstanceOf(Item::class, $this->persistedItem);
        $this->assertTrue($this->flushWasCalled);
        $this->assertEquals($data, $this->persistedItem->getData());
    }

    public function testRemove(): void
    {
        $item = new Item();
        $item->setData('data');

        $this->itemService->remove($item);
        $this->assertTrue($this->flushWasCalled);
        $this->assertTrue($this->removeWasCalled);
    }

    /**
     * @return EntityManagerInterface|MockObject
     */
    private function mockEntityManager(): MockObject
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('persist')->willReturnCallback(function (Item $item) {
            $this->persistedItem = $item;
        });
        $manager->method('flush')->willReturnCallback(function () {
            $this->flushWasCalled = true;
        });
        $manager->method('remove')->willReturnCallback(function () {
            $this->removeWasCalled = true;
        });

        return $manager;
    }
}
