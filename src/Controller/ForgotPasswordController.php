<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Repository\UserRepository;
use App\Form\ResetPasswordFormType;
use App\Form\ForgotPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ForgotPasswordController extends AbstractController
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/mot-de-passe-oublie', name: 'app_password')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        // 1 . Formulaire
        $form = $this->createForm(ForgotPasswordFormType::class);

        $form->handleRequest($request);

        // 2 . Traitement du formulaire

        if ($form->isSubmitted() && $form->isValid()) {
            // 3 . Si l'email renseigné par l'utilisateur est en base de donnée
            $email = $form->get('email')->getData();
            // dd($email);
            $user = $userRepository->findOneByEmail($email);
            // dd($user);

            // 4 . Envoyer une notification à l'utilisateur
            $this->addFlash('success', "Si votre adresse email existe, vous recevrez un mail pour réinitialiser votre mot de passe.");
            
            // 5 . Si user existe, on reset le password et on envoi par email le nouveau mot de passe
            if ($user) {

                // 5 . a - Créer un token qu'on va stocker en BDD
                $token = bin2hex(random_bytes(15));
                // dd($token);
                $user->setToken($token);

                $date = new DateTime();
                $date->modify('+10 minutes');

                $user->setTokenExpireAt($date);

                
                $this->em->flush();
                // dd($user);

                $mail = new Mail();
                    $vars = [
                        'link' => $this->generateUrl('app_password_update', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL )
                    ];
                $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Modification de votre mot de passe', 'forgotpassword.html' , $vars);
                
                
            }
            

        }


        return $this->render('password/index.html.twig', [
            'forgotPasswordForm' => $form->createView(),
        ]);
    }

    
    #[Route('/mot-de-passe/reset/{token}', name: 'app_password_update')]
    public function update(Request $request, $token, UserRepository $userRepository): Response
    {

        if(!$token){
            return $this->redirectToRoute("app_password");
        }

        $user = $userRepository->findOneByToken($token);

        $now = new DateTime();

        // si le user n'existe pas OU
        // on compare si la date actuelle ( une fois sur la page en question)
        // est supérieur à la date d'expiration du token que l'utilisateur à en bdd
        // alors on retourne à la page mot de passe oublié il devra refaire la procédure, car les 10 minutes du token ont du expirer
        if(!$user || $now > $user->getTokenExpireAt()){
            return $this->redirectToRoute("app_password");
        }

        $form = $this->createForm(ResetPasswordFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $user->setToken(null);
                $user->setTokenExpireAt(null);
                
                $this->em->flush();
                $this->addFlash(
                    'success',
                    'Votre mot de passe est correctement mise à jour'
                );
                return $this->redirectToRoute('app_login');
        }

        return $this->render('password/reset.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
