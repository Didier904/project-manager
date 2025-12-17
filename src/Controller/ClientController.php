<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // ADMIN voit tout
        if ($user->getEmail() === 'sowbassirou926@mail.com') {
            $clients = $clientRepository->findAllOrdered();
        } else {
            // Employés voient aussi tout (logique équipe)
            $clients = $clientRepository->findAllOrdered();
        }

        return $this->render('client/gestion.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // ADMIN UNIQUE
        if ($user->getEmail() !== 'sowbassirou926@mail.com') {
            throw $this->createAccessDeniedException('Seul l’admin peut créer un client.');
        }

        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Client partagé à toute l’équipe
            $client->setUser(null);

            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Client ajouté avec succès');

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('client/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User || $user->getEmail() !== 'sowbassirou926@mail.com') {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Client modifié');

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('client/edit.html.twig', [
            'form' => $form,
            'client' => $client,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User || $user->getEmail() !== 'sowbassirou926@mail.com') {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->get('_token'))) {
            $em->remove($client);
            $em->flush();
            $this->addFlash('success', 'Client supprimé');
        }

        return $this->redirectToRoute('app_dashboard');
    }
}
