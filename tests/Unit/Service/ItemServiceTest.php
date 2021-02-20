<?php

namespace App\Tests\Unit\Service;

use App\Entity\Item;
use App\Entity\User;
use App\Service\ItemService;
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

    protected function setUp(): void
    {
        $entityManager = $this->mockEntityManager();
        $this->itemService = new ItemService($entityManager);
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

    /**
     * @return EntityManagerInterface|MockObject
     */
    private function mockEntityManager()
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('persist')->willReturnCallback(function (Item $item) {
            $this->persistedItem = $item;
        });
        $manager->method('flush')->willReturnCallback(function () {
            $this->flushWasCalled = true;
        });

        return $manager;
    }
}
