<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Commande;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commercant')]
final class CommercantController extends AbstractController
{
    #[Route('/', name: 'app_commercant_dashboard', methods: ['GET'])]
    public function dashboard(ProduitRepository $produitRepository, CommandeRepository $commandeRepository): Response
    {
        $totalProduits = $produitRepository->count([]);
        $totalCommandes = $commandeRepository->count([]);
        $commandesRecents = $commandeRepository->findBy([], ['dateCreation' => 'DESC'], 5);
        $produitsRecents = $produitRepository->findBy([], ['dateCreation' => 'DESC'], 5);
        
        // Calculer les statistiques de ventes
        $commandesLivrees = $commandeRepository->findBy(['statut' => 'livree']);
        $totalRevenus = array_sum(array_map(fn($c) => $c->getTotalPrix(), $commandesLivrees));
        $totalVendus = $commandeRepository->createQueryBuilder('c')
            ->select('COUNT(cp.id)')
            ->leftJoin('c.produits', 'cp')
            ->where('c.statut = :statut')
            ->setParameter('statut', 'livree')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return $this->render('commercant/dashboard/index.html.twig', [
            'totalProduits' => $totalProduits,
            'totalCommandes' => $totalCommandes,
            'totalRevenus' => $totalRevenus,
            'totalVendus' => $totalVendus,
            'commandesRecents' => $commandesRecents,
            'produitsRecents' => $produitsRecents,
        ]);
    }

    #[Route('/produits', name: 'app_commercant_produits', methods: ['GET'])]
    public function produits(Request $request, ProduitRepository $produitRepository): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'ASC');
        
        $produits = $produitRepository->findBySearchAndSort($search, $sortBy, $order);
        
        return $this->render('commercant/produits/index.html.twig', [
            'produits' => $produits,
            'search' => $search,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }

    #[Route('/produits/new', name: 'app_commercant_produit_new', methods: ['GET', 'POST'])]
    public function newProduit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/produits';
                $safeName = bin2hex(random_bytes(16));
                $extension = $imageFile->guessExtension() ?: 'bin';
                $fileName = $safeName . '.' . $extension;

                $imageFile->move($targetDir, $fileName);
                $produit->setImage('uploads/produits/' . $fileName);
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès!');
            return $this->redirectToRoute('app_commercant_produits');
        }

        return $this->render('commercant/produits/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/produits/{id}', name: 'app_commercant_produit_show', methods: ['GET'])]
    public function showProduit(Produit $produit): Response
    {
        return $this->render('commercant/produits/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/produits/{id}/edit', name: 'app_commercant_produit_edit', methods: ['GET', 'POST'])]
    public function editProduit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $targetDir = $this->getParameter('kernel.project_dir') . '/public/uploads/produits';
                $safeName = bin2hex(random_bytes(16));
                $extension = $imageFile->guessExtension() ?: 'bin';
                $fileName = $safeName . '.' . $extension;

                $imageFile->move($targetDir, $fileName);
                $produit->setImage('uploads/produits/' . $fileName);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès!');
            return $this->redirectToRoute('app_commercant_produits');
        }

        return $this->render('commercant/produits/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/produits/{id}/toggle', name: 'app_commercant_produit_toggle', methods: ['POST'])]
    public function toggleProduit(Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($produit->getStatut() === 'disponible') {
            $produit->setStatut('archive');
            $this->addFlash('success', 'Produit désactivé avec succès!');
        } else {
            $produit->setStatut('disponible');
            $this->addFlash('success', 'Produit activé avec succès!');
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_commercant_produits');
    }

    #[Route('/produits/{id}/delete', name: 'app_commercant_produit_delete', methods: ['POST'])]
    public function deleteProduit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès!');
        }

        return $this->redirectToRoute('app_commercant_produits');
    }

    #[Route('/commandes', name: 'app_commercant_commandes', methods: ['GET'])]
    public function commandes(Request $request, CommandeRepository $commandeRepository): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'DESC');
        
        $commandes = $commandeRepository->findBySearchAndSort($search, $sortBy, $order);
        
        return $this->render('commercant/commandes/index.html.twig', [
            'commandes' => $commandes,
            'search' => $search,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }

    #[Route('/commandes/{id}', name: 'app_commercant_commande_show', methods: ['GET'])]
    public function showCommande(Commande $commande): Response
    {
        return $this->render('commercant/commandes/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commandes/{id}/confirm', name: 'app_commercant_commande_confirm', methods: ['POST'])]
    public function confirmCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $commande->setStatut('confirmee');
        $entityManager->flush();
        
        $this->addFlash('success', 'Commande confirmée avec succès!');
        return $this->redirectToRoute('app_commercant_commandes');
    }

    #[Route('/commandes/{id}/status', name: 'app_commercant_commande_status', methods: ['POST'])]
    public function updateStatus(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $newStatus = $request->request->get('status');
        $validStatuses = ['en_attente', 'confirmee', 'en_preparation', 'prete', 'en_livraison', 'livree', 'annulee'];
        
        if (in_array($newStatus, $validStatuses)) {
            $commande->setStatut($newStatus);
            $entityManager->flush();
            
            $this->addFlash('success', 'Statut de la commande mis à jour avec succès!');
        }

        return $this->redirectToRoute('app_commercant_commandes');
    }

    #[Route('/commandes/{id}/assign-livreur', name: 'app_commercant_commande_assign_livreur', methods: ['POST'])]
    public function assignLivreur(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $livreur = $request->request->get('livreur');
        
        if ($livreur) {
            $commande->setLivreur($livreur);
            $commande->setStatut('en_livraison');
            $entityManager->flush();
            
            $this->addFlash('success', 'Livreur affecté avec succès!');
        }

        return $this->redirectToRoute('app_commercant_commandes');
    }

    #[Route('/commandes/{id}/cancel', name: 'app_commercant_commande_cancel', methods: ['POST'])]
    public function cancelCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $commande->setStatut('annulee');
        $entityManager->flush();
        
        $this->addFlash('success', 'Commande annulée avec succès!');
        return $this->redirectToRoute('app_commercant_commandes');
    }

    #[Route('/commandes/{id}/contact', name: 'app_commercant_commande_contact', methods: ['GET', 'POST'])]
    public function contactClient(Request $request, Commande $commande): Response
    {
        $message = $request->request->get('message');
        
        if ($message) {
            // Ici vous pourriez intégrer un service d'envoi d'email
            $this->addFlash('success', 'Message envoyé au client avec succès!');
            return $this->redirectToRoute('app_commercant_commandes');
        }

        return $this->render('commercant/commandes/contact.html.twig', [
            'commande' => $commande,
        ]);
    }
}
