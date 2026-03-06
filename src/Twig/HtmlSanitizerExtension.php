<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HtmlSanitizerExtension extends AbstractExtension
{
    public function __construct(
        private readonly HtmlSanitizerInterface $richTextSanitizer,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sanitize_html', $this->sanitizeHtml(...), ['is_safe' => ['html']]),
        ];
    }

    public function sanitizeHtml(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return $this->richTextSanitizer->sanitize($html);
    }
}
