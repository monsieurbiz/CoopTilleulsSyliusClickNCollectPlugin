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

namespace CoopTilleuls\SyliusClickNCollectPlugin\Asset;

use Sylius\Bundle\ThemeBundle\Asset\PathResolverInterface;
use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\HttpFoundation\RequestStack;

final class AssetPackage implements PackageInterface
{
    public const PACKAGE_NAME = 'click-n-collect.assets.package';

    private PackageInterface $package;

    public function __construct(RequestStack $requestStack, ThemeContextInterface $themeContext, PathResolverInterface $pathResolver)
    {
        $this->package = new PathPackage(
            '/bundles/cooptilleulssyliusclickncollectplugin',
            new JsonManifestVersionStrategy(__DIR__ . '/../Resources/public/manifest.json'),
            new RequestStackContext($requestStack)
        );
    }

    public function getUrl(string $path): string
    {
        return $this->package->getUrl($path);
    }

    public function getVersion(string $path): string
    {
        return $this->package->getVersion($path);
    }
}
