<?php
// src/Controller/SalaireController.php
namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class SalaireController extends AbstractController
{
    #[Route('/salaire/saisir', name: 'salaire_saisir')]
    public function modifierSalaire(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nouveauSalaire = $request->request->get('salaire');
            if (is_numeric($nouveauSalaire) && $nouveauSalaire > 0) {
                // Créer un nouvel utilisateur (ou modifier l'existant)
                $utilisateur = new Utilisateur();
                $utilisateur->setSalaire($nouveauSalaire);
                $em->persist($utilisateur);
                $em->flush();

                // Redirection vers la page de saisie des dépenses
                return $this->redirectToRoute('depense_saisie');
            }

            $this->addFlash('error', 'Veuillez entrer un salaire valide.');
        }

        return $this->render('salaire/saisir.html.twig');
    }
}
