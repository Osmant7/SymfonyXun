<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use Stripe\Checkout\Session;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PayementController extends AbstractController
{
    #[Route('/commande/paiement/{id_order}', name: 'app_payement')]
    public function index($id_order, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // dans l'url il y'a l'id de la commande, or si à la main je met un autre id d'une commande d'un autre utilisateur j'accepte à sa page de paiement et c'est pas bon
        // pour sécuriser ici je fais pas un findOneById seulement, mais je vais dans un tableau récupéré l'id + le user actuelle si la commande n'est pas du meme utilisateur alors ça retourne null pas de commande et donc si pas de commande alors redirect à la page d'accueil
        $order = $orderRepository->findOneBy([
            'id' => $id_order,
            'user' => $this->getUser()
        ]);

        if(!$order) {
            return $this->redirectToRoute('app_home');
        }
        // dd($order);

        $products_for_stripe = [];
         
        foreach ($order->getOrderDetails() as $product) {
            // dans la variable ici on attribut un tableau avant le égal, afin d'avoir un tableau qui contiendra plusieurs
            // entrées qu'on va pouvoir enrichir plusieurs fois sans que les entrées précendente s'écrasent par les nouvelles
            $products_for_stripe[] = [

                'price_data' => [
                'currency' => 'eur',
                'unit_amount' => number_format($product->getProductPriceWt() * 100, 0, '', ''),
                'product_data' => [
                    'name' => $product->getProductName(),
                    'images' => [
                        $_ENV['DOMAIN'].'/uploads/'.$product->getProductIllustration()
                    ]
                ]
            ],
            'quantity' => $product->getProductQuantity(),
            ];

        }

        // ajout du transporteur dans la commande en passant une entrée dans notre tableau products_for_stripe
        // dd($order);
        $products_for_stripe[] = [
                'price_data' => [
                'currency' => 'eur',
                'unit_amount' => number_format($order->getCarrierPrice() * 100, 0, '', ''),
                'product_data' => [
                    'name' => 'Transporteur : '.$order->getCarrierName(),
                ]
            ],
            'quantity' => 1,
        ];

        // on envoies tout à stripe via la Session
        $checkout_session = Session::create([
                'customer_email' => $this->getUser()->getEmail(),
                'line_items' => [[
                    $products_for_stripe
            ]],
                'mode' => 'payment',
                'success_url' => $_ENV['DOMAIN'] . '/commande/merci/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $_ENV['DOMAIN'] . '/mon-panier/annulation',
        ]);

        // dd($checkout_session);
        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush();

        return $this->redirect($checkout_session->url);
    }

    #[Route('/commande/merci/{stripe_session_id}', name: 'app_payement_success')]
    public function success($stripe_session_id, OrderRepository $orderRepository, EntityManagerInterface $entityManager, Cart $cart): Response
    {
        $order = $orderRepository->findOneBy([
            'stripe_session_id' => $stripe_session_id,
            'user' => $this->getUser()
        ]);

        if(!$order) {
            return $this->redirectToRoute('app_home');
        }

        if($order->getState() == 1) {
            $order->setState(2);
            $cart->remove();
            $entityManager->flush();
        }

        //dd($order);
         return $this->render('payment/success.html.twig', [
            'order' => $order,
        ]);
    }

}
