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

namespace CoopTilleuls\SyliusClickNCollectPlugin\EventListener;

use CoopTilleuls\SyliusClickNCollectPlugin\Entity\ClickNCollectShipmentInterface;
use CoopTilleuls\SyliusClickNCollectPlugin\Entity\ClickNCollectShippingMethodInterface;
use CoopTilleuls\SyliusClickNCollectPlugin\Repository\CollectionTimeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Resource\Exception\RaceConditionException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * Prevents concurrent insertion of collection times.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class ShipmentCollectionTimeLockListener
{
    private EntityManagerInterface $entityManager;

    private CollectionTimeRepositoryInterface $collectionTimeRepository;

    private LockInterface $lock;

    public function __construct(EntityManagerInterface $entityManager, CollectionTimeRepositoryInterface $collectionTimeRepository, LockFactory $lockFactory)
    {
        $this->entityManager = $entityManager;
        $this->collectionTimeRepository = $collectionTimeRepository;
        $this->lock = $lockFactory->createLock('shipment-collection-time');
    }

    /**
     * @throws RaceConditionException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function onPreSelectShipping(ResourceControllerEvent $event): void
    {
        if (!$shipments = $this->getShipmentToChecks($event->getSubject())) {
            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();

        $this->lock->acquire(true);
        foreach ($shipments as $shipment) {
            $previousCollectionTime = $unitOfWork->getOriginalEntityData($shipment)['collectionTime'] ?? null;
            $newCollectionTime = $shipment->getCollectionTime();

            if ($previousCollectionTime !== $newCollectionTime && $this->collectionTimeRepository->isSlotFull($shipment->getLocation(), $shipment->getCollectionTime())) {
                $this->lock->release();

                throw new RaceConditionException();
            }
        }
    }

    public function onPostSelectShipping(): void
    {
        if ($this->lock->isAcquired()) {
            $this->lock->release();
        }
    }

    /**
     * @param mixed $order
     *
     * @return ClickNCollectShipmentInterface[]
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getShipmentToChecks($order): array
    {
        if (!$order instanceof OrderInterface) {
            return $order;
        }

        $filteredShipments = [];
        foreach ($order->getShipments() as $shipment) {
            if (
                $shipment instanceof ClickNCollectShipmentInterface
                && null !== $shipment->getCollectionTime()
                && $shipment->getMethod() instanceof ClickNCollectShippingMethodInterface
                && $shipment->getMethod()->isClickNCollect()
            ) {
                $filteredShipments[] = $shipment;
            }
        }

        return $filteredShipments;
    }
}
