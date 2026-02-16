<?php

namespace App\Controller;

use App\Entity\Partenaire;
use App\Form\PartenaireType;
use App\Repository\PartenaireRepository;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partenaire')]
final class PartenaireController extends AbstractController
{
    #[Route(name: 'app_partenaire_index', methods: ['GET'])]
    public function index(Request $request, PartenaireRepository $partenaireRepository): Response
    {
        // Récupérer les paramètres de recherche et tri
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'nom');
        $direction = $request->query->get('direction', 'ASC');

        // Vérifier que la direction est valide
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // Tri "métier" sécurisé (whitelist) + support du tri par nom de zone
        $allowedSorts = ['id', 'nom', 'type', 'email', 'telephone', 'zone'];
        if (!\in_array($sort, $allowedSorts, true)) {
            $sort = 'nom';
        }

        // Récupérer les partenaires avec recherche et tri
        if ($search) {
            $partenaires = $partenaireRepository->searchAndSort($search, $sort, $direction);
        } else {
            $qb = $partenaireRepository->createQueryBuilder('p')
                ->leftJoin('p.zone', 'z')
                ->addSelect('z');

            if ($sort === 'zone') {
                $qb->orderBy('z.nom', $direction);
            } else {
                $qb->orderBy('p.' . $sort, $direction);
            }

            $partenaires = $qb->getQuery()->getResult();
        }

        // Dashboard KPIs (sur la liste affichée)
        $total = \count($partenaires);
        $withZone = 0;
        $types = [];
        foreach ($partenaires as $p) {
            if ($p->getZone() !== null) {
                $withZone++;
            }
            $t = (string) ($p->getType() ?? '');
            if ($t !== '') {
                $types[$t] = ($types[$t] ?? 0) + 1;
            }
        }
        \arsort($types);

        return $this->render('partenaire/index.html.twig', [
            'partenaires' => $partenaires,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'kpi_total' => $total,
            'kpi_with_zone' => $withZone,
            'kpi_top_types' => \array_slice($types, 0, 3, true),
        ]);
    }

    #[Route('/api', name: 'app_partenaire_api', methods: ['GET'])]
    public function apiIndex(Request $request, PartenaireRepository $partenaireRepository): Response
    {
        // API "pro" : support recherche + tri identiques à l'écran HTML
        $search = $request->query->get('q', '');
        $sort = $request->query->get('sort', 'nom');
        $direction = $request->query->get('direction', 'ASC');

        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $allowedSorts = ['id', 'nom', 'type', 'email', 'telephone', 'zone'];
        if (!\in_array($sort, $allowedSorts, true)) {
            $sort = 'nom';
        }

        // Requête optimisée pour éviter le problème N+1 + alignée avec l'interface
        if ($search) {
            $partenaires = $partenaireRepository->searchAndSort($search, $sort, $direction);
        } else {
            $qb = $partenaireRepository->createQueryBuilder('p')
                ->leftJoin('p.zone', 'z')
                ->addSelect('z');

            if ($sort === 'zone') {
                $qb->orderBy('z.nom', $direction);
            } else {
                $qb->orderBy('p.' . $sort, $direction);
            }

            $partenaires = $qb->getQuery()->getResult();
        }

        $data = [];
        foreach ($partenaires as $partenaire) {
            $data[] = [
                'id' => $partenaire->getId(),
                'nom' => $partenaire->getNom(),
                'type' => $partenaire->getType(),
                'email' => $partenaire->getEmail(),
                'telephone' => $partenaire->getTelephone(),
                'adresse' => $partenaire->getAddresse(),
                'siteWeb' => $partenaire->getSiteweb(),
                'contrats' => array_map(fn ($c) => [
                    'dateDebut' => $c->getDateDebut()?->format('Y-m-d'),
                    'dateFin' => $c->getDateFin()?->format('Y-m-d'),
                ], $partenaire->getContrats()->toArray()),
                'zone' => $partenaire->getZone() ? [
                    'id' => $partenaire->getZone()->getId(),
                    'nom' => $partenaire->getZone()->getNom(),
                ] : null,
            ];
        }

        return $this->json([
            'meta' => [
                'count' => \count($data),
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'data' => $data,
        ]);
    }

    #[Route('/new', name: 'app_partenaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ZoneRepository $zoneRepository): Response
    {
        // Métier: impossible de créer un partenaire sans zones existantes
        if ($zoneRepository->count([]) === 0) {
            $this->addFlash('warning', 'Veuillez d’abord créer au moins une zone avant d’ajouter un partenaire.');
            return $this->redirectToRoute('app_zone_new');
        }

        $partenaire = new Partenaire();
        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partenaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_partenaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partenaire/new.html.twig', [
            'partenaire' => $partenaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partenaire_show', methods: ['GET'])]
    public function show(Partenaire $partenaire): Response
    {
        return $this->render('partenaire/show.html.twig', [
            'partenaire' => $partenaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_partenaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partenaire $partenaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PartenaireType::class, $partenaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_partenaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partenaire/edit.html.twig', [
            'partenaire' => $partenaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partenaire_delete', methods: ['POST'])]
    public function delete(Request $request, Partenaire $partenaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partenaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($partenaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_partenaire_index', [], Response::HTTP_SEE_OTHER);
    }
}