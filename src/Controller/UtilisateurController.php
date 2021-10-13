<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UtilisateurType;
use App\Repository\UserRepository;
use App\Service\Securizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/espace-prive/utilisateurs")
 */
class UtilisateurController extends AbstractController
{
    /**
     * @Route("/", name="utilisateur_index")
     */
    public function index(UserRepository $userRepository): Response
    { 
        $user_id = $this->getUser()->getId();
        $users = $userRepository->findUsers(['ROLE_USER']);

        return $this->render('utilisateur/index.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/new", name="utilisateur_new", methods="GET|POST")
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();

        $form = $this->createForm(UtilisateurType::class, $user, ['edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password)->setIsVerified(1);
            $user->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Le nouvel utilisateur a bien été enregistré');

            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/new.html.twig', [
            'user' => $user,
            'edit' => false,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="utilisateur_show", methods="GET")
     */
    public function show(User $user): Response
    {
        return $this->render('utilisateur/show.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/{id}/edit", name="utilisateur_edit", methods="GET|POST")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UtilisateurType::class, $user, ['edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'L\'utilisateur a bien été modifié');

            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/edit.html.twig', [
            'user' => $user,
            'edit' => true,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="utilisateur_delete", methods="GET")
     */
    public function delete(User $user): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé');
        return $this->redirectToRoute('utilisateur_index');
    }
}
