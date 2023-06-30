<?php
declare(strict_types=1);

namespace App\Entity\Shipping;

use CoopTilleuls\SyliusClickNCollectPlugin\Entity\ClickNCollectShippingMethod;
use CoopTilleuls\SyliusClickNCollectPlugin\Entity\ClickNCollectShippingMethodInterface;
use CoopTilleuls\SyliusClickNCollectPlugin\Entity\Location;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shipping_method")
 */
class ShippingMethod extends BaseShippingMethod implements ClickNCollectShippingMethodInterface
{
    use ClickNCollectShippingMethod {
        __construct as initializeShippingMethodLocations;
    }

    public function __construct()
    {
        parent::__construct();

        $this->initializeShippingMethodLocations();
    }

}
