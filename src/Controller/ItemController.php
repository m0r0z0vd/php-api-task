<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\ItemService;
use App\Service\PutFormDataParser;
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
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $data = (string)$request->get('data', '');

        if (!$data) {
            return new JsonResponse(['error' => 'No data parameter']);
        }

        /** @var User $user */
        $user = $this->getUser();
        $this->itemService->create($user, $data);

        return new JsonResponse([]);
    }

    /**
     * @Route("/item", name="item_update", methods={"PUT"})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $id = (int)PutFormDataParser::get('id', $content);

        if (!$id) {
            return new JsonResponse(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }
        
        $data = (string)PutFormDataParser::get('data', $content);

        if (!$data) {
            return new JsonResponse(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }
        
        $item = $this->itemRepository->find($id);

        if (!$item) {
            return new JsonResponse(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $this->itemService->update($item, $data);

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
            return new JsonResponse(['error' => 'No data parameter'], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->itemRepository->find($id);

        if (!$item) {
            return new JsonResponse(['error' => 'No item'], Response::HTTP_BAD_REQUEST);
        }

        $this->itemService->remove($item);

        return new JsonResponse([]);
    }
}
