<?php

/*
 * This file is part of Les-Tilleuls.coop's Click 'N' Collect project.
 *
 * (c) Les-Tilleuls.coop <contact@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CoopTilleuls\SyliusClickNCollectPlugin\Controller\Admin;

use CoopTilleuls\SyliusClickNCollectPlugin\CollectionTime\RecurrenceInstanceFinderInterface;
use CoopTilleuls\SyliusClickNCollectPlugin\Repository\CollectionTimeRepositoryInterface;
use DateTime;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Returns the list of upcoming shipments as Full Calendar events.
 *
 * @see https://fullcalendar.io/docs/event-object
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class CollectionsApiController
{
    private ObjectRepository $locationRepository;

    private CollectionTimeRepositoryInterface $collectionTimeRepository;

    private RecurrenceInstanceFinderInterface $recurrenceInstanceFinder;

    private RouterInterface $router;

    public function __construct(ObjectRepository $locationRepository, CollectionTimeRepositoryInterface $collectionTimeRepository, RecurrenceInstanceFinderInterface $recurrenceInstanceFinder, RouterInterface $router)
    {
        $this->locationRepository = $locationRepository;
        $this->collectionTimeRepository = $collectionTimeRepository;
        $this->recurrenceInstanceFinder = $recurrenceInstanceFinder;
        $this->router = $router;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __invoke(Request $request, string $locationCode): JsonResponse
    {
        try {
            $startDateTime = new DateTimeImmutable($request->query->get('start', 'now'));
            $endDateTime = new DateTimeImmutable($request->query->get('end', '+1 week'));
        } catch (Exception $e) {
            throw new BadRequestHttpException('Invalid date format', $e);
        }

        if (!$location = $this->locationRepository->findOneBy(['code' => $locationCode])) {
            throw new NotFoundHttpException(\sprintf('The location "%s" doesn\'t exist.', $locationCode));
        }

        $events = [];
        foreach ($this->collectionTimeRepository->findShipments($location, $startDateTime, $endDateTime) as $shipment) {
            $recurrence = ($this->recurrenceInstanceFinder)($shipment);

            $order = $shipment instanceof ShipmentInterface ? $shipment->getOrder() : null;
            $event = [
                'id' => $id = $recurrence->getStart()->format(DateTime::ATOM),
                'start' => $id,
                'end' => $recurrence->getEnd()->format(DateTime::ATOM),
            ];
            if ($order) {
                $event['title'] = '#' . $shipment->getOrder()->getNumber();
                $event['url'] = $this->router->generate('sylius_admin_order_show', ['id' => $order->getId()]);
            }
            $events[] = $event;
        }

        return new JsonResponse($events);
    }
}
