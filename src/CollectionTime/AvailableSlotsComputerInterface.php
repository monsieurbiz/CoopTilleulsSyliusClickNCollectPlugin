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

namespace CoopTilleuls\SyliusClickNCollectPlugin\CollectionTime;

use CoopTilleuls\SyliusClickNCollectPlugin\Entity\ClickNCollectShipmentInterface;
use CoopTilleuls\SyliusClickNCollectPlugin\Entity\LocationInterface;
use DateTimeInterface;
use InvalidArgumentException;
use Recurr\Recurrence;

/**
 * Computes available time slots.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface AvailableSlotsComputerInterface
{
    /**
     * @throws InvalidArgumentException
     *
     * @return Recurrence[]
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __invoke(ClickNCollectShipmentInterface $shipment, LocationInterface $location, ?DateTimeInterface $startDate = null, ?DateTimeInterface $endDate = null, bool $onlyFuture = false, int $limit = 732): array;
}
