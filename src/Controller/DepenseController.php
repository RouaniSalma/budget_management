<?php
// src/Controller/DepenseController.php
namespace App\Controller;

use App\Entity\Depense;
use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class DepenseController extends AbstractController
{
    #[Route('/depense/saisie', name: 'depense_saisie')]
public function saisieDepense(Request $request, EntityManagerInterface $em): Response
{
    $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy([], ['id' => 'DESC']);
    
    if (!$utilisateur) {
        throw $this->createAccessDeniedException('Veuillez entrer votre salaire d’abord.');
    }

    $depenses = $em->getRepository(Depense::class)->findBy(['utilisateur' => $utilisateur]);

    if ($request->isMethod('POST')) {
        $categorie = $request->request->get('categorie');
        $montant = $request->request->get('montant');
        $description = $request->request->get('description');
        $date_depense = $request->request->get('date_depense');

        if (is_numeric($montant) && $montant > 0) {
            $depense = new Depense();
            $depense->setCategorie($categorie);
            $depense->setDescription($description);
            $depense->setDateDepense(new \DateTime($date_depense));
            $depense->setMontant($montant);
            $depense->setUtilisateur($utilisateur);

            $em->persist($depense);
            $em->flush();

            return $this->redirectToRoute('depense_liste');
        }

        $this->addFlash('error', 'Veuillez entrer des informations valides.');
    }

    return $this->render('depense/saisie.html.twig', [
        'depenses' => $depenses,
        'salaire' => $utilisateur->getSalaire(),
    ]);
}
#[Route('/depense/liste', name: 'depense_liste')]
public function listeDepenses(EntityManagerInterface $em): Response
{
    $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy([], ['id' => 'DESC']);
    
    if (!$utilisateur) {
        throw $this->createAccessDeniedException('Veuillez entrer votre salaire d’abord.');
    }

    $depenses = $em->getRepository(Depense::class)->findBy(['utilisateur' => $utilisateur]);

    return $this->render('depense/liste.html.twig', [
        'depenses' => $depenses,
    ]);
}
#[Route('/depense/voir/{id}', name: 'depense_voir')]
public function voirDepense(int $id, EntityManagerInterface $em): Response
{
    $depense = $em->getRepository(Depense::class)->find($id);
    
    if (!$depense) {
        throw $this->createNotFoundException('Dépense non trouvée.');
    }

    return $this->render('depense/voir.html.twig', [
        'depense' => $depense,
    ]);
}
#[Route('/depense/modifier/{id}', name: 'depense_modifier')]
public function modifierDepense(int $id, Request $request, EntityManagerInterface $em): Response
{
    $depense = $em->getRepository(Depense::class)->find($id);

    if (!$depense) {
        throw $this->createNotFoundException('Dépense non trouvée.');
    }

    // Si le formulaire est soumis
    if ($request->isMethod('POST')) {
        $depense->setCategorie($request->request->get('categorie'));
        $depense->setMontant($request->request->get('montant'));
        $depense->setDescription($request->request->get('description'));
        $depense->setDateDepense(new \DateTime($request->request->get('date_depense')));

        $em->flush();

        return $this->redirectToRoute('depense_liste');
    }

    return $this->render('depense/modifier.html.twig', [
        'depense' => $depense,
    ]);
}
#[Route('/depense/supprimer/{id}', name: 'depense_supprimer')]
public function supprimerDepense(int $id, EntityManagerInterface $em): Response
{
    $depense = $em->getRepository(Depense::class)->find($id);

    if (!$depense) {
        throw $this->createNotFoundException('Dépense non trouvée.');
    }

    $em->remove($depense);
    $em->flush();

    return $this->redirectToRoute('depense_liste');
}

#[Route('/depense/recapitulatif', name: 'depense_recapitulatif')]
public function recapitulatif(EntityManagerInterface $em): Response
{
    // Récupérer l'utilisateur
    $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy([], ['id' => 'DESC']);
    
    if (!$utilisateur) {
        throw $this->createAccessDeniedException('Veuillez entrer votre salaire d’abord.');
    }

    // Récupérer les dépenses de l'utilisateur
    $depenses = $em->getRepository(Depense::class)->findBy(['utilisateur' => $utilisateur]);

    // Calculer le total des dépenses
    $totalDepenses = 0;
    foreach ($depenses as $depense) {
        $totalDepenses += $depense->getMontant();
    }

    // Récupérer le salaire de l'utilisateur
    $salaire = $utilisateur->getSalaire();
    $budgetRestant = $salaire - $totalDepenses;

    // Calculer le pourcentage de dépenses par rapport au salaire
    $pourcentageDepenses = ($salaire > 0) ? ($totalDepenses / $salaire) * 100 : 0;

    return $this->render('depense/recapitulatif.html.twig', [
        'depenses' => $depenses,
        'totalDepenses' => $totalDepenses,
        'budgetRestant' => $budgetRestant,
        'salaire' => $salaire,
        'pourcentageDepenses' => $pourcentageDepenses,
    ]);
}


}
