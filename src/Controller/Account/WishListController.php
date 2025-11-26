<?php

namespace App\Controller\Account;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WishListController extends AbstractController
{
    #[Route('/compte/liste-de-souhait', name: 'app_account_wish_list')]
    public function index(): Response
    {
        return $this->render('account/wish_list/index.html.twig');
    }

    #[Route('/compte/liste-de-souhait/add/{id}', name: 'app_account_wish_list_add')]
    public function add(ProductRepository $productRepository, $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // 1. récuperer l'objet du produit souhaité
        $product = $productRepository->findOneById($id);
        // 2. Si produit existant alors ajouter le produit à la wishlisht.
        if ($product) {
            $this->getUser()->addWishlist($product);
            // 3. sauvegarder en BDD
            $entityManager->flush();
        }

        $this->addFlash(
                'success',
                'Produit correctement ajouté à votre liste de souhait'
        );

        return $this->redirect($request->headers->get('referer'));
    }

     #[Route('/compte/liste-de-souhait/remove/{id}', name: 'app_account_wish_list_remove')]
    public function remove(ProductRepository $productRepository, $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // 1. récuperer l'objet du produit à supprimer
        $product = $productRepository->findOneById($id);

        // 2. Si produit existant alors supprimer le produit de la wishlisht.
        if ($product) {

            $this->addFlash('success', 'Produit correctement supprimé de votre liste de souhait');
            $this->getUser()->removeWishlist($product);
            // 3. sauvegarder en BDD
            $entityManager->flush();
        } else {
            $this->addFlash('danger', 'Produit introuvable');
        }

        return $this->redirect($request->headers->get('referer'));
    }
}
