<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User; // <-- Il manquait !
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chat')]
class ChatController extends AbstractController
{
    #[Route('/{recipientId}', name: 'app_chat', methods: ['GET', 'POST'])]
    public function index(
        int $recipientId,
        Request $request,
        MessageRepository $messageRepo,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        // Récupération du destinataire
        $recipient = $em->getRepository(User::class)->find($recipientId);

        if (!$recipient) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Messages entre l'utilisateur et le destinataire
        $messages = $messageRepo->findChatBetweenUsers($user, $recipient);

        // Nouveau message
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($user);
            $message->setRecipient($recipient);
            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('app_chat', ['recipientId' => $recipientId]);
        }

        return $this->render('chat/chat.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
            'recipient' => $recipient,
        ]);
    }
}
