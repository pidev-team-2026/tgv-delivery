<?php

namespace App\Controller;

use App\Entity\Livreur;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Form\ProduitType;
use App\Repository\LivreurRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commercant')]
final class CommercantController extends AbstractController
{
    // ════════════════════════════════════════════
    //  DASHBOARD
    // ════════════════════════════════════════════

    #[Route('/', name: 'wajih_commercant_dashboard', methods: ['GET'])]
    public function dashboard(ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
        $totalProduits  = $produitRepository->count([]);
        $totalCommandes = $commandeRepository->count([]);
        $commandesRecents = $commandeRepository->findBy([], ['dateCreation' => 'DESC'], 5);
        $produitsRecents  = $produitRepository->findBy([], ['dateCreation' => 'DESC'], 5);

        $commandesLivrees = $commandeRepository->findBy(['statut' => 'livree']);
        $totalRevenus = array_sum(array_map(fn($c) => $c->getTotalPrix(), $commandesLivrees));
        $totalVendus  = $commandeRepository->createQueryBuilder('c')
            ->select('COUNT(cp.id)')
            ->leftJoin('c.produits', 'cp')
            ->where('c.statut = :statut')
            ->setParameter('statut', 'livree')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return $this->render('commercant/dashboard/commercant_dashboard.html.twig', [
            'totalProduits'   => $totalProduits,
            'totalCommandes'  => $totalCommandes,
            'totalRevenus'    => $totalRevenus,
            'totalVendus'     => $totalVendus,
            'commandesRecents'=> $commandesRecents,
            'produitsRecents' => $produitsRecents,
        ]);
    }

    // ════════════════════════════════════════════
    //  PRODUITS
    // ════════════════════════════════════════════

    #[Route('/produits', name: 'wajih_commercant_produits', methods: ['GET'])]
    public function produits(Request $request, ProduitRepository $produitRepository): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'id');
        $order  = $request->query->get('order', 'ASC');

        return $this->render('commercant/produits/index.html.twig', [
            'produits' => $produitRepository->findBySearchAndSort($search, $sortBy, $order),
            'search'   => $search,
            'sortBy'   => $sortBy,
            'order'    => $order,
        ]);
    }

    #[Route('/produits/new', name: 'wajih_commercant_produit_new', methods: ['GET', 'POST'])]
    public function newProduit(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $form    = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $dir      = $this->getParameter('kernel.project_dir') . '/public/uploads/produits';
                $fileName = bin2hex(random_bytes(16)) . '.' . ($imageFile->guessExtension() ?: 'bin');
                $imageFile->move($dir, $fileName);
                $produit->setImage('uploads/produits/' . $fileName);
            }
            $em->persist($produit);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('wajih_commercant_produits');
        }

        return $this->render('commercant/produits/new.html.twig', ['produit' => $produit, 'form' => $form]);
    }

    #[Route('/produits/{id}', name: 'wajih_commercant_produit_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showProduit(Produit $produit): Response
    {
        return $this->render('commercant/produits/show.html.twig', ['produit' => $produit]);
    }

    #[Route('/produits/{id}/edit', name: 'wajih_commercant_produit_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editProduit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $dir      = $this->getParameter('kernel.project_dir') . '/public/uploads/produits';
                $fileName = bin2hex(random_bytes(16)) . '.' . ($imageFile->guessExtension() ?: 'bin');
                $imageFile->move($dir, $fileName);
                $produit->setImage('uploads/produits/' . $fileName);
            }
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('wajih_commercant_produits');
        }

        return $this->render('commercant/produits/edit.html.twig', ['produit' => $produit, 'form' => $form]);
    }

    #[Route('/produits/{id}/toggle', name: 'wajih_commercant_produit_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleProduit(Produit $produit, EntityManagerInterface $em): Response
    {
        $produit->setStatut($produit->getStatut() === 'disponible' ? 'archive' : 'disponible');
        $em->flush();
        $this->addFlash('success', 'Produit ' . ($produit->getStatut() === 'disponible' ? 'activé' : 'désactivé') . '.');
        return $this->redirectToRoute('wajih_commercant_produits');
    }

    #[Route('/produits/{id}/delete', name: 'wajih_commercant_produit_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteProduit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Produit supprimé.');
        }
        return $this->redirectToRoute('wajih_commercant_produits');
    }

    // ════════════════════════════════════════════
    //  COMMANDES — LISTE
    // ════════════════════════════════════════════

    #[Route('/commandes', name: 'wajih_commercant_commandes', methods: ['GET'])]
    public function commandes(Request $request, CommandeRepository $commandeRepository): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'id');
        $order  = $request->query->get('order', 'DESC');

        return $this->render('commercant/commandes/index.html.twig', [
            'commandes' => $commandeRepository->findBySearchAndSort($search, $sortBy, $order),
            'search'    => $search,
            'sortBy'    => $sortBy,
            'order'     => $order,
        ]);
    }

    // ════════════════════════════════════════════
    //  COMMANDES — DÉTAIL + ACTIONS
    // ════════════════════════════════════════════

    #[Route('/commandes/{id}', name: 'wajih_commercant_commande_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showCommande(Commande $commande, LivreurRepository $livreurRepo): Response
    {
        return $this->render('commercant/commandes/show_commercant.html.twig', [
            'commande'            => $commande,
            'livreursPropres'     => $livreurRepo->findPropresDisponibles(),
            'livreursPartenaires' => $livreurRepo->findPartenairesDisponibles(),
        ]);
    }

    #[Route('/commandes/{id}/confirm', name: 'wajih_commercant_commande_confirm', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function confirmCommande(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('confirm' . $commande->getId(), $request->request->get('_token'))) {
            $commande->setStatut('confirmee');
            $em->flush();
            $this->addFlash('success', 'Commande confirmée !');
        }
        return $this->redirectToRoute('wajih_commercant_commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/commandes/{id}/status', name: 'wajih_commercant_commande_status', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function updateStatus(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $valid = ['en_attente','confirmee','en_preparation','prete','en_livraison','livree','annulee'];
        $new   = $request->request->get('status');
        if (in_array($new, $valid)) {
            $commande->setStatut($new);
            $em->flush();
            $this->addFlash('success', 'Statut mis à jour : ' . $commande->getStatutLibelle());
        }
        return $this->redirectToRoute('wajih_commercant_commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/commandes/{id}/cancel', name: 'wajih_commercant_commande_cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancelCommande(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('cancel' . $commande->getId(), $request->request->get('_token'))) {
            if ($commande->getLivreur()) {
                $commande->getLivreur()->setStatut(Livreur::STATUT_DISPONIBLE);
            }
            $commande->setLivreur(null);
            $commande->setStatut('annulee');
            $em->flush();
            $this->addFlash('success', 'Commande annulée.');
        }
        return $this->redirectToRoute('wajih_commercant_commandes');
    }

    #[Route('/commandes/{id}/contact', name: 'wajih_commercant_commande_contact', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function contactClient(Request $request, Commande $commande): Response
    {
        if ($request->request->get('message')) {
            $this->addFlash('success', 'Message envoyé au client !');
            return $this->redirectToRoute('wajih_commercant_commandes');
        }
        return $this->render('commercant/commandes/contact.html.twig', ['commande' => $commande]);
    }

    // ════════════════════════════════════════════
    //  LIVREURS — GESTION COMPLÈTE
    // ════════════════════════════════════════════

    /** Liste tous les livreurs */
    #[Route('/livreurs', name: 'app_commercant_livreurs', methods: ['GET'])]
    public function livreurs(LivreurRepository $livreurRepo): Response
    {
        return $this->render('commercant/livreurs/index.html.twig', [
            'livreursPropres'     => $livreurRepo->findPropresDisponibles(),
            'livreursPartenaires' => $livreurRepo->findPartenairesDisponibles(),
            'livreursOccupes'     => $livreurRepo->findBy(['statut' => Livreur::STATUT_OCCUPE]),
            'livreursInactifs'    => $livreurRepo->findBy(['statut' => Livreur::STATUT_INACTIF]),
            'tous'                => $livreurRepo->findBy([], ['statut' => 'ASC', 'nom' => 'ASC']),
        ]);
    }

    /** Ajouter un livreur (GET form + POST save) */
    #[Route('/livreurs/new', name: 'app_commercant_livreur_new', methods: ['GET', 'POST'])]
    public function newLivreur(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $livreur = new Livreur();
            $livreur->setNom(trim($request->request->get('nom', '')));
            $livreur->setPrenom(trim($request->request->get('prenom', '')));
            $livreur->setTelephone(trim($request->request->get('telephone', '')));
            $livreur->setEmail($request->request->get('email') ?: null);
            $livreur->setType($request->request->get('type', Livreur::TYPE_PROPRE));
            $livreur->setVehicule($request->request->get('vehicule') ?: null);
            $livreur->setImmatriculation($request->request->get('immatriculation') ?: null);
            $livreur->setZonesCouvertes($request->request->get('zonesCouvertes') ?: null);
            $livreur->setSocietePartenaire($request->request->get('societePartenaire') ?: null);
            $livreur->setStatut(Livreur::STATUT_DISPONIBLE);

            $em->persist($livreur);
            $em->flush();

            $this->addFlash('success', '✅ Livreur ' . $livreur->getNomComplet() . ' ajouté !');
            return $this->redirectToRoute('app_commercant_livreurs');
        }

        return $this->render('commercant/livreurs/new.html.twig');
    }

    /** Modifier statut livreur (disponible / occupe / inactif) */
    #[Route('/livreurs/{id}/statut', name: 'app_commercant_livreur_statut', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function changerStatutLivreur(Livreur $livreur, Request $request, EntityManagerInterface $em): Response
    {
        $valid = [Livreur::STATUT_DISPONIBLE, Livreur::STATUT_OCCUPE, Livreur::STATUT_INACTIF];
        $new   = $request->request->get('statut');
        if (in_array($new, $valid)) {
            $livreur->setStatut($new);
            $em->flush();
            $this->addFlash('success', 'Statut de ' . $livreur->getNomComplet() . ' mis à jour.');
        }
        return $this->redirectToRoute('app_commercant_livreurs');
    }

    /** Supprimer un livreur */
    #[Route('/livreurs/{id}/delete', name: 'app_commercant_livreur_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteLivreur(Livreur $livreur, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_livreur' . $livreur->getId(), $request->request->get('_token'))) {
            // Désassigner des commandes actives
            foreach ($livreur->getCommandes() as $cmd) {
                $cmd->setLivreur(null);
            }
            $em->remove($livreur);
            $em->flush();
            $this->addFlash('success', 'Livreur supprimé.');
        }
        return $this->redirectToRoute('app_commercant_livreurs');
    }

    // ════════════════════════════════════════════
    //  LIVREURS — API ASSIGNATION (AJAX)
    // ════════════════════════════════════════════

    /** GET — liste JSON des livreurs dispo pour la modal */
    #[Route('/commandes/{id}/livreurs-dispo', name: 'app_commercant_livreurs_dispo', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getLivreursDispo(Commande $commande, LivreurRepository $livreurRepo): JsonResponse
    {
        $fmt = fn(Livreur $l) => [
            'id'                => $l->getId(),
            'nomComplet'        => $l->getNomComplet(),
            'telephone'         => $l->getTelephone(),
            'vehiculeLibelle'   => $l->getVehiculeLibelle(),
            'zonesCouvertes'    => $l->getZonesCouvertes() ?? '—',
            'societePartenaire' => $l->getSocietePartenaire(),
            'note'              => $l->getNote(),
            'nombreLivraisons'  => $l->getNombreLivraisons(),
            'type'              => $l->getType(),
        ];

        return $this->json([
            'propres'       => array_map($fmt, $livreurRepo->findPropresDisponibles()),
            'partenaires'   => array_map($fmt, $livreurRepo->findPartenairesDisponibles()),
            'livreurActuel' => $commande->getLivreur() ? $fmt($commande->getLivreur()) : null,
        ]);
    }

    /**
     * POST — assigne le livreur (appelé en AJAX depuis la modal du show)
     * ✅ FIX : utilise (int) cast au lieu de getInt() pour éviter les erreurs
     */
    #[Route('/commandes/{id}/assign-livreur', name: 'app_commercant_commande_assign_livreur', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function assignLivreur(
        Request $request,
        Commande $commande,
        LivreurRepository $livreurRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('assign' . $commande->getId(), $request->request->get('_token'))) {
            return $this->json(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
        }

        // ✅ FIX : cast explicite en int (getInt() peut retourner 0 si le champ est mal envoyé)
        $livreurId = (int) $request->request->get('livreur_id', 0);

        if ($livreurId <= 0) {
            return $this->json(['success' => false, 'message' => 'Aucun livreur sélectionné (id=' . $livreurId . ').'], 400);
        }

        $livreur = $livreurRepo->find($livreurId);
        if (!$livreur) {
            return $this->json(['success' => false, 'message' => 'Livreur #' . $livreurId . ' introuvable en base.'], 404);
        }

        if (!$livreur->isDisponible()) {
            return $this->json(['success' => false, 'message' => $livreur->getNomComplet() . ' n\'est pas disponible.'], 400);
        }

        // Libérer l'ancien livreur si différent
        $ancien = $commande->getLivreur();
        if ($ancien && $ancien->getId() !== $livreur->getId()) {
            $ancien->setStatut(Livreur::STATUT_DISPONIBLE);
        }

        $commande->setLivreur($livreur);
        $commande->setStatut('en_livraison');
        $livreur->setStatut(Livreur::STATUT_OCCUPE);
        $livreur->incrementLivraisons();
        $em->flush();

        return $this->json([
            'success'    => true,
            'message'    => $livreur->getNomComplet() . ' assigné avec succès !',
            'livreurNom' => $livreur->getNomComplet(),
            'livreurTel' => $livreur->getTelephone(),
            'statut'     => 'en_livraison',
        ]);
    }

    /** POST — désassigne le livreur */
    #[Route('/commandes/{id}/desassigner-livreur', name: 'app_commercant_commande_desassign_livreur', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function desassignerLivreur(Request $request, Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isCsrfTokenValid('desassign' . $commande->getId(), $request->request->get('_token'))) {
            return $this->json(['success' => false, 'message' => 'Token invalide.'], 403);
        }

        if ($commande->getLivreur()) {
            $commande->getLivreur()->setStatut(Livreur::STATUT_DISPONIBLE);
        }
        $commande->setLivreur(null);
        $commande->setStatut('prete');
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Livreur désassigné.']);
    }
}
