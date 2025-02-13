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

namespace CoopTilleuls\SyliusClickNCollectPlugin\Entity;

use DateTimeInterface;
use Sylius\Component\Core\Model\ShipmentInterface;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface ClickNCollectShipmentInterface extends ShipmentInterface
{
    public function isClickNCollect(): bool;

    public function getLocation(): ?LocationInterface;

    public function setLocation(?LocationInterface $location): void;

    public function getCollectionTime(): ?DateTimeInterface;

    public function setCollectionTime(?DateTimeInterface $collectionTime);

    public function getPin(): ?string;

    public function setPin(?string $pin): void;
}
