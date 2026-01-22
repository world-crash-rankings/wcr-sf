<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Psr\Log\LoggerInterface;
use App\Event\ScoreAdded;

final readonly class ScoreDiscordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChatterInterface $chatter,
        private ?LoggerInterface $logger = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScoreAdded::class => 'onScoreAdded',
        ];
    }

    /**
     * @param ScoreAdded $event
     * @throws TransportExceptionInterface
     */
    public function onScoreAdded(ScoreAdded $event): void
    {
        try {
            $score = $event->getScore();
            $player = $score->getPlayer();
            $zone = $score->getZone();

            $message = sprintf(
                "🏆 **NEW SCORE**\n\n👤 **%s** just scored **%s** on **%s**\n🎮 Platform: %s",
                $player->getName(),
                number_format((float) $score->getScore()) . ' $',
                $zone->getName(),
                $score->getPlatform()?->name
            );

            $this->sendMessage($message);

            $this->logger?->info($message, [
                'score_id' => $score->getId(),
            ]);
        } catch (\Exception $e) {
            $this->logger?->error('Error sending Discord notification for score', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendMessage(string $message): void
    {
        $chatMessage = new ChatMessage($message);
        $this->chatter->send($chatMessage);
    }
}
