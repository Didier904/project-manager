<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chat')]
class ChatController extends AbstractController
{
    #[Route('/{recipientId?}', name: 'app_chat', methods: ['GET', 'POST'])]
    public function chat(
        ?int $recipientId,
        Request $request,
        EntityManagerInterface $em,
        MessageRepository $messageRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        // =========================
        // Liste des conversations (colonne gauche)
        // =========================
        $conversations = $messageRepo->findUserConversations($user);

        // =========================
        // Conversation à droite
        // =========================
        $recipient = null;
        $messages = [];

        if ($recipientId) {
            $recipient = $em->getRepository(User::class)->find($recipientId);
            if (!$recipient) {
                throw $this->createNotFoundException('Utilisateur introuvable');
            }

            $messages = $messageRepo->findChatBetweenUsers($user, $recipient);

            // Marquer comme lus
            foreach ($messages as $msg) {
                if ($msg->getRecipient() === $user && !$msg->isRead()) {
                    $msg->setIsRead(true);
                }
            }
        }

        // =========================
        // Formulaire d'envoi (toujours créé)
        // =========================
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $recipient) {
            $message
                ->setSender($user)
                ->setRecipient($recipient)
                ->setCreatedAt(new \DateTime())
                ->setIsRead(false);

            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('app_chat', ['recipientId' => $recipient->getId()]);
        }

        // =========================
        // Messages non lus pour navbar
        // =========================
        $unreadData = $this->getUnreadMessages($messageRepo);

        return $this->render('chat/inbox.html.twig', [
            'conversations'  => $conversations,
            'recipient'      => $recipient,
            'messages'       => $messages,
            'form'           => $form->createView(),
            'unreadCount'    => $unreadData['count'],
            'unreadMessages' => $unreadData['messages'],
        ]);
    }

    // =========================
    // Supprimer un message
    // =========================
    #[Route('/message/{id}/delete/{recipientId}', name: 'app_chat_delete_message', methods: ['POST'])]
    public function deleteMessage(
        Message $message,
        int $recipientId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($message->getSender() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $message->getId(), $request->request->get('_token'))) {
            $em->remove($message);
            $em->flush();
        }

        return $this->redirectToRoute('app_chat', ['recipientId' => $recipientId]);
    }

    // =========================
    // Messages non lus pour navbar
    // =========================
    private function getUnreadMessages(MessageRepository $messageRepo, int $limit = 5): array
    {
        $user = $this->getUser();
        if (!$user) return ['messages' => [], 'count' => 0];

        $messages = $messageRepo->createQueryBuilder('m')
            ->where('m.recipient = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'messages' => $messages,
            'count'    => count($messages),
        ];
    }
}
