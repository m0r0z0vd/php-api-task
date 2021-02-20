<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\ItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends AbstractController
{
    /** @var ItemService */
    private $itemRepository;

    /** @var ItemService */
    private $itemService;

    public function __construct(ItemRepository $itemRepository, ItemService $itemService)
    {
        $this->itemRepository = $itemRepository;
        $this->itemService = $itemService;
    }

    /**
     * @Route("/item", name="item_list", methods={"GET"})
     * @IsGranted("ROLE_USER")
     * @return JsonResponse
     */
    public function list(): JsonResponse
    {
        $items = $this->itemRepository->findBy(['user' => $this->getUser()]);
        $arrayData = $this->itemService->toArrayData($items);

        return new JsonResponse($arrayData);
    }

    /**
     * @Route("/item", name="item_create", methods={"POST"})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param ItemService $itemService
     * @return JsonResponse
     */
    public function create(Request $request, ItemService $itemService): JsonResponse
    {
        $data = (string)$request->get('data', '');

        if (!$data) {
            return $this->json(['error' => 'No data parameter']);
        }

        /** @var User $user */
        $user = $this->getUser();
        $itemService->create($user, $data);

        return new JsonResponse([]);
    }

    /**
     * @Route("/item/{id}", name="items_delete", methods={"DELETE"})
     * @IsGranted("ROLE_USER")
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        if (!$id) {
            return $this->json(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return $this->json(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $this->itemService->remove($item);

        return new JsonResponse([]);
    }
}
