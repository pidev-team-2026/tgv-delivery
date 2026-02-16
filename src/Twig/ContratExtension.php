<?php

namespace App\Twig;

use App\Repository\ContratRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class ContratExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly ContratRepository $contratRepository,
    ) {
    }

    public function getGlobals(): array
    {
        try {
            $count = \count($this->contratRepository->findExpiresSansNotification());
        } catch (\Throwable) {
            $count = 0;
        }

        return [
            'contrats_expires_sans_notification_count' => $count,
        ];
    }
}
