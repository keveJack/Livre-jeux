<?php

namespace App\Controller;

use App\Entity\Partie;
use App\Entity\Personnage;
use App\Form\PersonnageType;
use App\Repository\AventureRepository;
use App\Repository\EtapeRepository;
use App\Repository\PartieRepository;
use App\Repository\PersonnageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class JouerController extends AbstractController
{
    #[Route('/jouer', name: 'app_jouer')]
    public function index(PersonnageRepository $personnageRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $personnages = $personnageRepository->findBy(["user"=>$this->getUser()]);
        return $this->render('jouer/index.html.twig', [
            'personnages' => $personnages,
        ]);
    }

    #[Route('/jouer/new', name: 'app_jouer_new')]
    public function newPersonnage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $personnage = new Personnage();
        $form = $this->createForm(PersonnageType::class, $personnage);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $personnage->setUser($this->getUser());
            $entityManager->persist($personnage);
            $entityManager->flush();

            return $this->redirectToRoute('app_jouer', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('jouer/new_personnage.html.twig', [
            'form' => $form->createView(),
            'personnage' => $personnage,
        ]);

    }
    #[Route('/jouer/aventures/{idPersonnage}', name: 'app_choix_aventure', methods: ['GET'])]
    public function afficherAventures(AventureRepository $aventureRepository, PersonnageRepository $personnageRepository, $idPersonnage): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $personnage = $personnageRepository->find($idPersonnage);
        // Verify ownership
    if ($personnage->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException('You do not have access to this character.');
    }
        $aventures = $aventureRepository->findAll();
        return $this->render('jouer/aventures.html.twig', [
            'personnage' => $personnage, 'aventures' => $aventures,

        ]);
    }
    #[Route('/jouer/aventure/{idPersonnage}/{idAventure}', name: 'app_start_aventure', methods: ['GET'])]
    public function demarrerAventure(PersonnageRepository $personnageRepository, AventureRepository $aventureRepository,
        PartieRepository $partieRepository, $idPersonnage, $idAventure, EntityManagerInterface $entityManager): Response {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $personnage = $personnageRepository->find($idPersonnage);
        // Verify ownership
    if ($personnage->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException('You do not have access to this character.');
    }
        $aventure = $aventureRepository->find($idAventure);
        $partie = $partieRepository->findOneBy(array('aventurier' => $personnage, 'aventure' => $aventure));
        $isNewPartie = !isset($partie);

        if ($isNewPartie) {
            $isNewPartie = true;
            $partie = new Partie();
            $partie->setAventurier($personnage);
            $partie->setAventure($aventure);
            $partie->setEtape($aventure->getPremiereEtape());
            $partie->setDatePartie(new \DateTime('now'));
            $entityManager->persist($partie);
            $entityManager->flush();
        }

        return $this->render('jouer/aventure-start.html.twig', [
            'personnage' => $personnage,
            'aventure' => $aventure,
            'partie' => $partie,
            'isNewPartie' => $isNewPartie,
        ]);

    }

    #[Route('/jouer/etape/{idPersonnage}/{idAventure}/{idPartie}/{idEtape}', name: 'app_play_aventure', methods: ['GET'])]
    public function playAventure($idPartie, $idEtape, $idPersonnage,$idAventure,AventureRepository $aventureRepository, PartieRepository $partieRepository, PersonnageRepository $personnageRepository,EtapeRepository $etapeRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $partie = $partieRepository->find($idPartie);
        $etape = $etapeRepository->find($idEtape);
        $personnage = $personnageRepository->find($idPersonnage);
        // Verify ownership
    if ($personnage->getUser() !== $this->getUser()) {
        throw $this->createAccessDeniedException('You do not have access to this character.');
    }
        $aventure = $aventureRepository->find($idAventure);
        
            $partie->setEtape($etape);
            $entityManager->persist($partie);
            $entityManager->flush();

        if ($etape->getFinAventure() != null) {
            return $this->render('jouer/aventure-end.html.twig', [
                'partie' => $partie,'personnage' => $personnage,'aventure'=>$aventure,
            ]);
        } else {
            return $this->render('jouer/aventure-play.html.twig', [
                'partie' => $partie, 'personnage' => $personnage,'aventure'=>$aventure,
            ]);
        }
    }

}
