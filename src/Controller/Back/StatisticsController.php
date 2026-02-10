<?php

namespace App\Controller\Back;

use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/statistics')]
class StatisticsController extends AbstractController
{
    public function __construct(
        private readonly ReclamationRepository $reclamationRepository
    ) {
    }

    #[Route('', name: 'app_back_statistics_index', methods: ['GET'])]
    public function index(): Response
    {
        $now = new \DateTimeImmutable('today');

        // Données par jour (30 derniers jours)
        $fromDay = $now->modify('-30 days')->setTime(0, 0);
        $reclamationsByDay = $this->groupByDay(
            $this->reclamationRepository->findCreatedBetween($fromDay, $now),
            $fromDay,
            $now
        );
        $predictionDay = $this->predictNext($reclamationsByDay, 7);

        // Données par semaine (12 dernières semaines, lundi = début)
        $fromWeek = $now->modify('-12 weeks')->setTime(0, 0);
        $reclamationsByWeek = $this->groupByWeek(
            $this->reclamationRepository->findCreatedBetween($fromWeek, $now),
            $fromWeek,
            $now
        );
        $predictionWeek = $this->predictNext($reclamationsByWeek, 4);

        // Données par mois (12 derniers mois)
        $fromMonth = $now->modify('-12 months')->modify('first day of this month')->setTime(0, 0);
        $reclamationsByMonth = $this->groupByMonth(
            $this->reclamationRepository->findCreatedBetween($fromMonth, $now),
            $fromMonth,
            $now
        );
        $predictionMonth = $this->predictNext($reclamationsByMonth, 3);

        return $this->render('back/statistics/index.html.twig', [
            'stats_day' => $reclamationsByDay,
            'stats_day_labels' => array_keys($reclamationsByDay),
            'stats_day_values' => array_values($reclamationsByDay),
            'stats_week' => $reclamationsByWeek,
            'stats_week_labels' => array_keys($reclamationsByWeek),
            'stats_week_values' => array_values($reclamationsByWeek),
            'stats_month' => $reclamationsByMonth,
            'stats_month_labels' => array_keys($reclamationsByMonth),
            'stats_month_values' => array_values($reclamationsByMonth),
            'prediction_day' => $predictionDay,
            'prediction_week' => $predictionWeek,
            'prediction_month' => $predictionMonth,
        ]);
    }

    /**
     * @param Reclamation[] $reclamations
     * @return array<string, int>
     */
    private function groupByDay(array $reclamations, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $result = [];
        $current = \DateTimeImmutable::createFromInterface($from);
        $end = \DateTimeImmutable::createFromInterface($to);
        while ($current <= $end) {
            $result[$current->format('Y-m-d')] = 0;
            $current = $current->modify('+1 day');
        }
        foreach ($reclamations as $r) {
            $key = $r->getCreatedAt()->format('Y-m-d');
            if (!isset($result[$key])) {
                $result[$key] = 0;
            }
            $result[$key]++;
        }
        ksort($result);
        return $result;
    }

    /**
     * @param Reclamation[] $reclamations
     * @return array<string, int>
     */
    private function groupByWeek(array $reclamations, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $result = [];
        $current = \DateTimeImmutable::createFromInterface($from);
        $end = \DateTimeImmutable::createFromInterface($to);
        while ($current <= $end) {
            $monday = $current->modify('monday this week');
            $key = $monday->format('Y-\WW');
            $result[$key] = 0;
            $current = $current->modify('+1 week');
        }
        foreach ($reclamations as $r) {
            $d = \DateTimeImmutable::createFromInterface($r->getCreatedAt());
            $monday = $d->modify('monday this week');
            $key = $monday->format('Y-\WW');
            if (!isset($result[$key])) {
                $result[$key] = 0;
            }
            $result[$key]++;
        }
        ksort($result);
        return $result;
    }

    /**
     * @param Reclamation[] $reclamations
     * @return array<string, int>
     */
    private function groupByMonth(array $reclamations, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $result = [];
        $current = \DateTimeImmutable::createFromInterface($from)->modify('first day of this month');
        $end = \DateTimeImmutable::createFromInterface($to);
        while ($current <= $end) {
            $key = $current->format('Y-m');
            $result[$key] = 0;
            $current = $current->modify('+1 month');
        }
        foreach ($reclamations as $r) {
            $key = $r->getCreatedAt()->format('Y-m');
            if (!isset($result[$key])) {
                $result[$key] = 0;
            }
            $result[$key]++;
        }
        ksort($result);
        return $result;
    }

    /**
     * Prédiction par régression linéaire simple (tendance + extrapolation).
     * @param array<string, int> $series
     * @return array<int, float>
     */
    private function predictNext(array $series, int $nextCount): array
    {
        $values = array_values($series);
        $n = \count($values);
        if ($n < 2 || $nextCount <= 0) {
            return array_fill(0, $nextCount, $n ? (float) $values[$n - 1] : 0.0);
        }
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $values[$i];
            $sumXY += $i * $values[$i];
            $sumX2 += $i * $i;
        }
        $denom = $n * $sumX2 - $sumX * $sumX;
        $a = $denom != 0 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
        $b = ($sumY - $a * $sumX) / $n;
        $predictions = [];
        for ($i = 0; $i < $nextCount; $i++) {
            $predictions[] = max(0, round($a * ($n + $i) + $b, 1));
        }
        return $predictions;
    }
}
