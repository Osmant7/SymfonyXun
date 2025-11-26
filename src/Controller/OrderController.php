<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class OrderController extends AbstractController
{
    /*
    * 1ère étape du tunnel d'achat
    * Choix de l'adresse de livraison et du transporteur
    */
    #[Route('/commande/livraison', name: 'app_order')]
    public function index(): Response
    {
        $addresses = $this->getUser()->getAddresses();

        // ici dans le if on met count() pour compter le nombre d'adresse qu'il ya
        // normalement on aura mit !$addresses mais ici $adresses égal à une collection
        // et quand c'est une collection on doit compter via count() pour vérifier si y'a une adresse
        if (count($addresses) == 0) {
            return $this->redirectToRoute('app_account_address_form');
        }

        // transiter la validation du formulaire ( choix du transporteur et adresse)
        // vers /commande/recapitulatif grâce à 'action' this->generateUrl('app_order_summary')
        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $addresses,
            'action' => $this->generateUrl('app_order_summary')
        ]);

        return $this->render('order/index.html.twig', [
            'deliveryForm' => $form->createView(),
        ]);
    }

    /*
    * 2eme étape du tunnel d'achat
    * Récap de la commande de l'utilisateur
    * Insértion en base de donnée
    * Préparation du paiement vers Stripe
    */
    #[Route('/commande/recapitulatif', name: 'app_order_summary')]
    public function add(Request $request,EntityManagerInterface $entityManager, Cart $cart): Response
    {
        // si la personne ici, va dans l'url et fait "entrée" yaura erreur car là
        // c'est une visite de page donc via GET et non POST
        // on va donc rediriger l'utilisateur si il fait un entré dans l'url lorsqu'il est
        // dans /commande/recapitulatif pour éviter cette erreur 
        // il sera redirigé dans le panier
        if ($request->getMethod() != 'POST') {
            return $this->redirectToRoute('app_cart');
        }

        $products = $cart->getCart();

        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $this->getUser()->getAddresses()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Création de la chaine adresse
            // on récupère les données de l'adresse que l'on transformera via la variable $address en une chaine de caractere
            $addressObj = $form->get('addresses')->getData();

            $address = $addressObj->getFirstname().' '.$addressObj->getLastname().'</br>';
            $address .= $addressObj->getAddress().'</br>';
            $address .= $addressObj->getPostal().' '.$addressObj->getCity().'</br>';
            $address .= $addressObj->getCountry().'</br>';
            $address .= $addressObj->getPhone().'</br>';
            
            // stocker les infos en BDD
            // on va initié un nouvelle commande -> Order ( entité Order )
            // à laquelle on va seté la date, le state ( l'état de la commande ), nom du transporteur et son prix
            // et on va seté le setDelivery avec comme parametre la chaine de caractere de l'adresse qu'on a réglé juste au dessus
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt(new \DateTime());
            $order->setState(1);
            $order->setCarrierName($form->get('carriers')->getData()->getName());
            $order->setCarrierPrice($form->get('carriers')->getData()->getPrice());
            $order->setDelivery($address);
            

            // on va faire un foreach de $products ( $products qui récupère le contenu du panier )
            // on va initié un nouvel entité OrderDetail()
            // et à chaque boucle on va dans la variable $orderDetail seté les proprieté 
            // productName etc par les valeurs du contenu de notre panier
            // sachant que si on fait un dd de $products on à chaque produit entré dans le panier
            // et pour chaque produit on à 2 objets
            // 'object' avec tous ce qui concerne le produit donc le nom description illustration prix etc
            // 'qty' pour la quantité de ce produit dans le panier
            // de ce fait quand on va seté, on va aller chercher dans 'object' les informations du produit.
            // et enfin dans la variable $order ( entité Order qu'on a initié juste au dessus)
            // contient une function addOrderDetail à laquelle on envoie comme parametre notre variable $orderDetail
            // ici il est question de Cascade ( permission ) permettre à une entité de créer une autre entité
            // en gros on dit a symfony quel sont les permissions que je t'accord lorsque tu dois faire ou manipuler une entité depuis une autre entité
            foreach ($products as $product) {
                // dd($product['object']->getName());
                $orderDetail = new OrderDetail();
                $orderDetail->setProductName($product['object']->getName());
                $orderDetail->setProductIllustration($product['object']->getIllustration());
                $orderDetail->setProductPrice($product['object']->getPrice());
                $orderDetail->setProductTva($product['object']->getTva());
                $orderDetail->setProductQuantity($product['qty']);
                $order->addOrderDetail($orderDetail);
            }

            $entityManager->persist($order);
            $entityManager->flush();


        }

        return $this->render('order/summary.html.twig', [
            'choices' => $form->getData(),
            'cart' => $products,
            'order' => $order,
            'totalWt' => $cart->getTotalWt()
        ]);
    }
}
