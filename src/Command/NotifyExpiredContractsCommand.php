<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
#[AsCommand(
    name: 'app:contrats:notify-expired',
    description: 'Marque les contrats expirés comme notifiés (à exécuter via cron pour la surveillance)',
)]
final class NotifyExpiredContractsCommand extends Command
{
    public function __construct(
        private readonly ContratRepository $contratRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $contrats = $this->contratRepository->findExpiresSansNotification();

        if (empty($contrats)) {
            $io->success('Aucun contrat expiré sans notification.');
            return Command::SUCCESS;
        }

        $now = new \DateTime();

        foreach ($contrats as $contrat) {
            $contrat->setNotificationEnvoyeeAt($now);
            $this->entityManager->flush();

            $io->writeln(sprintf(
                '  → Contrat #%d (Partenaire: %s) — expiré le %s',
                $contrat->getId(),
                $contrat->getPartenaire()->getNom(),
                $contrat->getDateFin()?->format('d/m/Y')
            ));
        }

        $io->success(sprintf('%d contrat(s) marqué(s) comme notifiés.', \count($contrats)));
        return Command::SUCCESS;
    }
}
