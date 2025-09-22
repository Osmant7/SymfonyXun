<?php

namespace App\Classe;

use Symfony\Component\HttpFoundation\RequestStack;

class Cart
{


    public function __construct(private RequestStack $requestStack)
    {
        
    }

    /*
    * add()
    * Fonction permettant l'ajout d'un produit au panier
    */
    public function add($product)
    {
        // appeller la session CART de symfony
        
        $cart = $this->getCart();

        // ajouter une quantité +1 à mon produit
        if (isset($cart[$product->getId()])) {
            // si mon produit est déjà dans mon panier alors j'ajoute une quantité +1
            $cart[$product->getId()] = [
                'object' => $product,
                'qty' => $cart[$product->getId()]['qty'] + 1
            ];
        } else {
            // sinon je créer mon produit dans mon panier et je met la quantité à 1
            $cart[$product->getId()] = [
                'object' => $product,
                'qty' => 1
            ];
        }
        

        // créer ma session Cart
        $this->requestStack->getSession()->set('cart', $cart);

    }

    /*
    * decrease()
    * Fonction permettant la suppression d'une quantité d'un produit au panier
    */
    public function decrease($id)
    {
        // suppression d'un produit du panier
        // si dans le panier la qty du produit est supérieur à 1 alors on diminu de 1
        // sinon si c'est égal ou inférieur à 1 alors on supprime le produit du panier
        $cart = $this->getCart();

        if ($cart[$id]['qty'] > 1) {
             $cart[$id]['qty'] = $cart[$id]['qty'] - 1;
        } else {
            unset($cart[$id]);
        }

        $this->requestStack->getSession()->set('cart', $cart);

    }
    
    /*
    * fullQuantity()
    * Fonction retournant le nombre total de produit au panier
    */
    public function fullQuantity()
    {
        $cart = $this->getCart();
        $quantity = 0;

        // ici un if si $cart n'existe pas donc que dans la session le panier est vide inéxistant 
        // alors retourne 0 ( return $quantity qui est déja défini à 0)
        if(!isset($cart)) {
            return $quantity;
        }

        foreach ($cart as $product) {
            $quantity = $quantity + $product['qty'];
        }
        
        return $quantity;
    }


    /*
    * getTotalWt()
    * Fonction retournant le prix total des produits au panier
    */
    // avoir le prix total du panier
    // en faisant un calcul pour chaque produit du panier on multiplie le prix du produit ( en passant par 'object')
    // par la quantité du produit et le tout on l'ajoute à $price qui est initié à 0
    // et tout ceci dans un boucle foreach qui va faire cette opération pour chaque produit du panier 
    public function getTotalWt()
    {
        $cart = $this->getCart();
        $price = 0;

        if(!isset($cart)) {
            return $price;
        }

        foreach ($cart as $product) {
            $price = $price + ($product['object']->getPriceWt() * $product['qty']);
        }
        
        return $price;
    }

    /*
    * remove()
    * Fonction permettant de supprimer totalement le panier
    */

    public function remove()
    {
        return $this->requestStack->getSession()->remove('cart');
    }

    /*
    * getCart()
    * Fonction retournant le panier en récupérant la session CART de symfony
    */
    public function getCart()
    {
        return $this->requestStack->getSession()->get('cart');
    }

}