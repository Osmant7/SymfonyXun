<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProductController extends AbstractController
{

    // ici dans les parametre de la function index on utilise une méthode différente de d'habitude ( voir l'autre methode classique dans categoryController)
    // l'auto mapping MapEntity, qui permet d'éviter de faire appel a la repository
    // et permet de pas utiliser le findOneBySlug 
    // donc on écrit moins de code avec cette méthode qui fais simplement appelle cette fois ci
    // a l'entité product
    // et dans l'auto mapping on précise le slug 'slug'
    // et le résultat et le même que la méthode classique
    #[Route('/produit/{slug}', name: 'app_product')]
    public function index(#[MapEntity(mapping: ['slug' => 'slug'])] Product $product): Response
    {

        if (!$product) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('product/index.html.twig', [
            'product' => $product,
        ]);
    }
}
