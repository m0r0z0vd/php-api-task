<?php

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ItemService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Item[] $items
     * @return array
     */
    public function toArrayData(array $items): array
    {
        $array = [];

        foreach ($items as $item) {
            $array[] = [
                'id' => $item->getId(),
                'data' => $item->getData(),
                'created_at' => $item->getCreatedAt(),
                'updated_at' => $item->getUpdatedAt()
            ];
        }

        return $array;
    }

    /**
     * @param User $user
     * @param string $data
     */
    public function create(User $user, string $data): void
    {
        $item = new Item();
        $item->setUser($user);
        $item->setData($data);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    /**
     * @param Item $item
     */
    public function remove(Item $item): void
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }
}
