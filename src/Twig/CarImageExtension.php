<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CarImageExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('car_image', $this->carImage(...)),
        ];
    }

    /**
     * Convert car name to image filename
     * Example: "Custom Pickup" -> "custompickup"
     */
    public function carImage(string $carName): string
    {
        // Remove all non-alphabetic characters and convert to lowercase
        return strtolower((string) preg_replace('/[^a-zA-Z]/', '', $carName));
    }
}
