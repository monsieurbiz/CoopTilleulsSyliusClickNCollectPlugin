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

namespace CoopTilleuls\SyliusClickNCollectPlugin\Fixture\Factory;

use CoopTilleuls\SyliusClickNCollectPlugin\CollectionTime\AvailableSlotsComputerInterface;
use CoopTilleuls\SyliusClickNCollectPlugin\Entity\ClickNCollectShipmentInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use SM\Factory\FactoryInterface as StateMachineFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\OrderExampleFactory as BaseOrderExampleFactory;
use Sylius\Component\Core\Checker\OrderPaymentMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Checker\OrderShippingMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class OrderExampleFactory extends BaseOrderExampleFactory
{
    private AvailableSlotsComputerInterface $availableSlotsComputer;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(FactoryInterface $orderFactory, FactoryInterface $orderItemFactory, OrderItemQuantityModifierInterface $orderItemQuantityModifier, EntityManagerInterface $orderManager, RepositoryInterface $channelRepository, RepositoryInterface $customerRepository, ProductRepositoryInterface $productRepository, RepositoryInterface $countryRepository, PaymentMethodRepositoryInterface $paymentMethodRepository, ShippingMethodRepositoryInterface $shippingMethodRepository, FactoryInterface $addressFactory, StateMachineFactoryInterface $stateMachineFactory, OrderShippingMethodSelectionRequirementCheckerInterface $orderShippingMethodSelectionRequirementChecker, OrderPaymentMethodSelectionRequirementCheckerInterface $orderPaymentMethodSelectionRequirementChecker, AvailableSlotsComputerInterface $availableSlotsComputer)
    {
        parent::__construct($orderFactory, $orderItemFactory, $orderItemQuantityModifier, $orderManager, $channelRepository, $customerRepository, $productRepository, $countryRepository, $paymentMethodRepository, $shippingMethodRepository, $addressFactory, $stateMachineFactory, $orderShippingMethodSelectionRequirementChecker, $orderPaymentMethodSelectionRequirementChecker);
        $this->availableSlotsComputer = $availableSlotsComputer;
    }

    protected function selectShipping(OrderInterface $order, DateTimeInterface $createdAt): void
    {
        parent::selectShipping($order, $createdAt);

        foreach ($order->getShipments() as $shipment) {
            if ((null === $method = $shipment->getMethod()) || 'click_n_collect' !== $method->getCode()) {
                continue;
            }

            $shipment->setLocation($this->faker->randomElement($method->getLocations()));
            $this->setCollectionTime($shipment);
        }

        $this->orderManager->flush();
    }

    private function setCollectionTime(ClickNCollectShipmentInterface $shipment): void
    {
        $startDate = null;
        $endDate = null;
        if ($shippedAt = $shipment->getShippedAt()) {
            $shippedAtImmutable = DateTimeImmutable::createFromMutable($shippedAt);
            $startDate = $shippedAtImmutable->sub('-3 days');
            $endDate = $shippedAtImmutable->add('+1 days');
        }

        $recurrences = ($this->availableSlotsComputer)($shipment, $shipment->getLocation(), $startDate, $endDate, false, 10);
        if (null !== $recurrence = array_pop($recurrences)) {
            $shipment->setCollectionTime(DateTimeImmutable::createFromMutable($recurrence->getStart()));
        }
    }
}
