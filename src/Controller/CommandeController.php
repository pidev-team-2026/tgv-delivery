<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    private const CODES_PROMO = [
        'TGV10'     => ['type' => 'pct',      'valeur' => 10, 'label' => '10% de réduction'],
        'TGV20'     => ['type' => 'pct',      'valeur' => 20, 'label' => '20% de réduction'],
        'LIVFREE'   => ['type' => 'livraison','valeur' => 0,  'label' => 'Livraison gratuite'],
        'BIENVENUE' => ['type' => 'fixe',     'valeur' => 5,  'label' => '5 TND de réduction'],
    ];

    private const ESTIMATION = [
        'tunis' => 30, 'ariana' => 35, 'ben arous' => 35, 'manouba' => 40,
        'nabeul' => 60, 'zaghouan' => 55, 'bizerte' => 70, 'béja' => 100,
        'jendouba' => 120, 'kef' => 130, 'siliana' => 120, 'sousse' => 90,
        'monastir' => 95, 'mahdia' => 110, 'sfax' => 120, 'kairouan' => 110,
        'kasserine' => 140, 'sidi bouzid' => 130, 'gabès' => 150,
        'médenine' => 160, 'tataouine' => 200, 'gafsa' => 150,
        'tozeur' => 170, 'kébili' => 180,
    ];

    // ── ADMIN CRUD ────────────────────────────

    #[Route(name: 'wajih_commande_index', methods: ['GET'])]
    public function index(Request $request, CommandeRepository $commandeRepository): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'id');
        $order  = $request->query->get('order', 'DESC');

        $commandes = $commandeRepository->findBySearchAndSort($search, $sortBy, $order);
        if (empty($commandes)) {
            $commandes = $commandeRepository->findAll();
        }

        return $this->render('admin/commande/index.html.twig', [
            'commandes' => $commandes,
            'search' => $search,
            'sortBy' => $sortBy,
            'order'  => $order,
        ]);
    }

    #[Route('/new', name: 'wajih_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();
            return $this->redirectToRoute('wajih_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form'     => $form,
        ]);
    }


    #[Route('/api/promo/verifier', name: 'api_promo_verifier', methods: ['POST'])]
    public function verifierPromo(Request $request): JsonResponse
    {
        $data  = json_decode($request->getContent(), true);
        $code  = strtoupper(trim($data['code'] ?? ''));
        $total = floatval($data['total'] ?? 0);

        if (!$code) {
            return $this->json(['success' => false, 'message' => 'Code vide'], 400);
        }

        if (!isset(self::CODES_PROMO[$code])) {
            return $this->json(['success' => false, 'message' => 'Code promo invalide ❌']);
        }

        $promo = self::CODES_PROMO[$code];
        $remise = 0;
        $fraisLivraison = 7.0;

        match($promo['type']) {
            'pct'       => $remise = round($total * $promo['valeur'] / 100, 3),
            'fixe'      => $remise = floatval($promo['valeur']),
            'livraison' => $fraisLivraison = 0.0,
            default     => null,
        };

        return $this->json([
            'success'        => true,
            'code'           => $code,
            'label'          => $promo['label'],
            'remise'         => $remise,
            'fraisLivraison' => $fraisLivraison,
        ]);
    }

    #[Route('/api/livraison/estimation', name: 'api_livraison_estimation', methods: ['POST'])]
    public function estimationLivraison(Request $request): JsonResponse
    {
        $data        = json_decode($request->getContent(), true);
        $gouvernorat = strtolower(trim($data['gouvernorat'] ?? ''));
        $minutes     = self::ESTIMATION[$gouvernorat] ?? 60;

        $h = floor($minutes / 60);
        $m = $minutes % 60;
        $label = $minutes < 60
            ? "Livraison estimée : {$minutes} min"
            : "Livraison estimée : {$h}h" . ($m ? str_pad($m, 2, '0', STR_PAD_LEFT) . "min" : "");

        return $this->json(['success' => true, 'minutes' => $minutes, 'label' => $label]);
    }

    #[Route('/api/client/commande', name: 'api_client_commande_new', methods: ['POST'])]
    public function apiCreate(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || empty($data['items'])) {
            return $this->json(['success' => false, 'message' => 'Données invalides ou panier vide'], 400);
        }

        $items          = $data['items'];
        $total          = array_sum(array_map(fn($i) => floatval($i['price']) * intval($i['qty']), $items));
        $remise         = floatval($data['remise'] ?? 0);
        $fraisLivraison = floatval($data['fraisLivraison'] ?? 7.0);
        $gouvernorat    = strtolower(trim($data['gouvernorat'] ?? ''));
        $estimation     = self::ESTIMATION[$gouvernorat] ?? 60;

        $commande = new Commande();
        $commande->setNomClient(trim(($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? '')));
        $commande->setEmail($data['email'] ?? null);
        $commande->setTelephone($data['telephone'] ?? '');
        $commande->setAdresseLivraison($data['adresse'] ?? '');
        $commande->setVille(($data['gouvernorat'] ?? '') . ' - ' . ($data['ville'] ?? ''));
        $commande->setCodePostal($data['codePostal'] ?? '');
        $commande->setModePaiement($data['paiement'] ?? 'especes');
        $commande->setStatut('en_attente');
        $commande->setFraisLivraison($fraisLivraison);
        $commande->setRemise($remise);
        $commande->setCodePromo($data['codePromo'] ?? null);
        $commande->setTotalPrix(max(0, $total - $remise));
        $commande->setGouvernorat($data['gouvernorat'] ?? '');
        $commande->setEstimationLivraison($estimation);
        $commande->setNotes(json_encode($items));

        $entityManager->persist($commande);
        $entityManager->flush();

        return $this->json([
            'success'    => true,
            'id'         => $commande->getId(),
            'reference'  => $commande->getReference(),
            'total'      => $commande->getMontantTotal(),
            'estimation' => $estimation,
        ]);
    }

    #[Route('/{id}', name: 'wajih_commande_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', ['commande' => $commande]);
    }

    #[Route('/{id}/edit', name: 'wajih_commande_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('wajih_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', ['commande' => $commande, 'form' => $form]);
    }

    #[Route('/{id}', name: 'wajih_commande_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }
        return $this->redirectToRoute('wajih_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
