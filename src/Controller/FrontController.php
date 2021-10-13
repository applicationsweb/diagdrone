<?php

namespace App\Controller;

use App\Entity\Tchat;
use App\Service\Securizer;
use App\Repository\UserRepository;
use App\Repository\TchatRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/")
 */
class FrontController extends AbstractController
{
    /**
     * @Route("/", name="front_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, TchatRepository $tchatRepository, Securizer $securizer): Response
    {
        $users = [];
        $unread_messages = [];

        if($this->getUser() !== null) {
            if($securizer->isGranted($this->getUser(), 'ROLE_ADMIN')) {
                $users = $userRepository->findUsers();
            } else if($securizer->isGranted($this->getUser(), 'ROLE_USER')) {
                $users = $userRepository->findAdministrators();
            }

            // Récupère le nombre de messages non lus pour chaque utilisateur
            foreach($users as $u) {
                $unread_messages[]= $tchatRepository->findUnread($u->getId(), $this->getUser()->getId());
            }
        }

        return $this->render('front/index.html.twig', [
            'users' => $users,
            'unread_messages' => $unread_messages
        ]);
    }

    /**
     * Ouvre une communication
     * 
     * @Route("/open-tchat", name="front_open_tchat", methods="POST", options = { "expose" = true })
     */
    public function openTchat(Request $request, UserRepository $userRepository, TchatRepository $tchatRepository)
    {
        if($request->isXmlHttpRequest()) {
            $user_id = $request->request->get("id");
            $user = $userRepository->findOneById(intval($user_id)); // Récupère le destinataire sélectionné
            // Si le destinataire est bien un admin ou un utilisateur
            if(!empty($user) && (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_USER', $user->getRoles()))) {
                $messages = $tchatRepository->findCommunicationByReceiverAndSender(intval($user_id), $this->getUser()->getId());
                $manager = $this->getDoctrine()->getManager();
                foreach($messages as $message) {
                    if($message->getReceiver() == $this->getUser()) {
                        $message->setIsRead(1);
                        $manager->flush();
                    }
                }
            } 

            return $this->render('front/_tchat.html.twig', [
                'messages' => $messages
            ]);
        }
    }

    /**
     * Envoi un message
     * 
     * @Route("/send-message", name="front_send_message", methods="POST", options = { "expose" = true })
     */
    public function sendMessage(Request $request, UserRepository $userRepository)
    {
        if($request->isXmlHttpRequest()) {
            $receiver_id = $request->request->get("receiver");
            $receiver = $userRepository->findOneById(intval($receiver_id)); // Récupère le récepteur du message
            // Si le destinataire est bien un admin ou un utilisateur
            if(!empty($receiver) && (in_array('ROLE_ADMIN', $receiver->getRoles()) || in_array('ROLE_USER', $receiver->getRoles()))) {
                $message = urldecode($request->request->get("message"));
                $manager = $this->getDoctrine()->getManager();
                $tchat = new Tchat();
                $tchat->setMessage($message)
                    ->setReceiver($receiver)
                    ->setSender($this->getUser())
                    ->setCreated(new \DateTime())
                    ->setIsRead(0);
                $manager->persist($tchat);
                $manager->flush();
            } else {
                return new Response(false);
            }

            return $this->render('front/_tchat-message-unread.html.twig', [
                'messages' => [$tchat]
            ]);
        } else {
            return new Response(false);
        }
    }

    /**
     * Récupère les messages envoyés lors d'une discussion ouverte
     * 
     * @Route("/check-message", name="front_check_message", methods="POST", options = { "expose" = true })
     */
    public function checkMessage(Request $request, UserRepository $userRepository, TchatRepository $tchatRepository)
    {
        if($request->isXmlHttpRequest()) {
            $sender_id = $request->request->get("sender");
            $sender = $userRepository->findOneById(intval($sender_id)); // Récupère l'emetteur du message
            // Si le sender est bien un admin ou un utilisateur
            if(!empty($sender) && (in_array('ROLE_ADMIN', $sender->getRoles()) || in_array('ROLE_USER', $sender->getRoles()))) {
                // Récupère les messages non lus
                $messages = $tchatRepository->findUnReadMessage($this->getUser()->getId(), $sender_id);
                $manager = $this->getDoctrine()->getManager();
                foreach($messages as $message) {
                    $message->setIsRead(1);
                    $manager->flush();
                }
            } else {
                $messages = [];
            }

            return $this->render('front/_tchat-message-unread.html.twig', [
                'messages' => $messages
            ]);
        }
    }
}
