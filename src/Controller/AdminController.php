<?php

namespace App\Controller;

use App\Entity\Livreur;
use App\Repository\LivreurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    // ════════════════════════════════════════════
    //  LIVREURS — GESTION ADMIN
    // ════════════════════════════════════════════

    /** Liste tous les livreurs pour l'admin */
    #[Route('/livreurs', name: 'admin_livreurs', methods: ['GET'])]
    public function livreurs(LivreurRepository $livreurRepo): Response
    {
        return $this->render('admin/livreurs/index.html.twig', [
            'livreursPropres'     => $livreurRepo->findPropresDisponibles(),
            'livreursPartenaires' => $livreurRepo->findPartenairesDisponibles(),
            'livreursOccupes'     => $livreurRepo->findBy(['statut' => Livreur::STATUT_OCCUPE]),
            'livreursInactifs'    => $livreurRepo->findBy(['statut' => Livreur::STATUT_INACTIF]),
            'tous'                => $livreurRepo->findBy([], ['statut' => 'ASC', 'nom' => 'ASC']),
        ]);
    }

    /** Ajouter un livreur (GET form + POST save) */
    #[Route('/livreurs/new', name: 'admin_livreur_new', methods: ['GET', 'POST'])]
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
            return $this->redirectToRoute('admin_livreurs');
        }

        return $this->render('admin/livreurs/new.html.twig');
    }

    /** Modifier statut livreur (disponible / occupe / inactif) */
    #[Route('/livreurs/{id}/statut', name: 'admin_livreur_statut', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function changerStatutLivreur(Livreur $livreur, Request $request, EntityManagerInterface $em): Response
    {
        $valid = [Livreur::STATUT_DISPONIBLE, Livreur::STATUT_OCCUPE, Livreur::STATUT_INACTIF];
        $new   = $request->request->get('statut');
        if (in_array($new, $valid)) {
            $livreur->setStatut($new);
            $em->flush();
            $this->addFlash('success', 'Statut de ' . $livreur->getNomComplet() . ' mis à jour.');
        }
        return $this->redirectToRoute('admin_livreurs');
    }

    /** Supprimer un livreur */
    #[Route('/livreurs/{id}/delete', name: 'admin_livreur_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
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
        return $this->redirectToRoute('admin_livreurs');
    }
}
