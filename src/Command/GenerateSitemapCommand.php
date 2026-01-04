<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CountryRepository;
use App\Repository\PlayerRepository;
use App\Repository\ZoneRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:generate-sitemap',
    description: 'Generate sitemap.xml file',
)]
class GenerateSitemapCommand extends Command
{
    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly PlayerRepository $playerRepository,
        private readonly CountryRepository $countryRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $urlset = $xml->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->appendChild($urlset);

        // Static pages
        $staticPages = [
            ['route' => 'app_home', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['route' => 'zones', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'scores_lastadded', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['route' => 'scores_lastachieved', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['route' => 'rankings_ap', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['route' => 'players_list', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['route' => 'player_comparison', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['route' => 'country_view', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['route' => 'videos', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ];

        foreach ($staticPages as $page) {
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate($page['route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
                $page['priority'],
                $page['changefreq']
            );
        }

        // Video types
        $videoTypes = ['score', 'nonglitch', 'damage', 'multi', 'live', 'ps2', 'xbox50', 'xbox60', 'gc50', 'gc60'];
        foreach ($videoTypes as $type) {
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate('videos', ['type' => $type], UrlGeneratorInterface::ABSOLUTE_URL),
                '0.6',
                'weekly'
            );
        }

        // Zones
        $zones = $this->zoneRepository->findAll();
        foreach ($zones as $zone) {
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate('zone', ['id' => $zone->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                '0.8',
                'daily'
            );
        }

        // Players
        $players = $this->playerRepository->findAll();
        foreach ($players as $player) {
            // Player main page
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate(
                    'player_view',
                    ['nameUrl' => $player->getNameUrl()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '0.6',
                'weekly'
            );

            // Player info page
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate(
                    'player_info',
                    ['nameUrl' => $player->getNameUrl()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '0.5',
                'weekly'
            );
        }

        // Countries
        $countries = $this->countryRepository->findAll();
        foreach ($countries as $country) {
            $this->addUrl(
                $xml,
                $urlset,
                $this->urlGenerator->generate(
                    'country_view',
                    ['nameUrl' => $country->getNameUrl()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                '0.6',
                'weekly'
            );
        }

        // Save sitemap
        $sitemapPath = $this->projectDir . '/public/sitemap.xml';
        $xml->save($sitemapPath);

        $io->success('Sitemap generated successfully at: ' . $sitemapPath);

        return Command::SUCCESS;
    }

    private function addUrl(
        \DOMDocument $xml,
        \DOMElement $urlset,
        string $loc,
        string $priority,
        string $changefreq
    ): void {
        $url = $xml->createElement('url');

        $locElement = $xml->createElement('loc', htmlspecialchars($loc));
        $url->appendChild($locElement);

        $lastmodElement = $xml->createElement('lastmod', date('Y-m-d'));
        $url->appendChild($lastmodElement);

        $changefreqElement = $xml->createElement('changefreq', $changefreq);
        $url->appendChild($changefreqElement);

        $priorityElement = $xml->createElement('priority', $priority);
        $url->appendChild($priorityElement);

        $urlset->appendChild($url);
    }
}
