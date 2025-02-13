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
use InvalidArgumentException;
use Recurr\Recurrence;
use RuntimeException;

/**
 * Finds a Recurrence instance for a given shipment.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface RecurrenceInstanceFinderInterface
{
    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function __invoke(ClickNCollectShipmentInterface $shipment): Recurrence;
}
