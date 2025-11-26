<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Repository\HeaderRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(HeaderRepository $headerRepository, ProductRepository $productRepository): Response
    {
        // $mail = new Mail();
        // $vars = [
        //     'firstname' => 'John doe'
        // ];        
        //     $mail->send('xxxx', 'xxxx'.' '.'xxxx', 'Bienvenue sur projet Xun', 'welcome.html' , $vars);
            
        return $this->render('home/index.html.twig', [
            'headers' => $headerRepository->findAll(),
            'productsInHompage' => $productRepository->findByIsHomepage(true),
        ]);
    }
}
