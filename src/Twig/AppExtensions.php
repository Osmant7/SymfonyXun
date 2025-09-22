<?php 

namespace App\Twig;

use App\Classe\Cart;
use Twig\TwigFilter;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\AbstractExtension;
use App\Repository\CategoryRepository;

class AppExtensions extends AbstractExtension implements GlobalsInterface
{

    private $categoryRepository;
    private $cart;

    public function __construct(CategoryRepository $categoryRepository, Cart $cart)
    {
        $this->categoryRepository = $categoryRepository;
        $this->cart = $cart;
    }
    // on créer un filtre twig qu'on nomme price, et qui utilisera (via la doc twig) le formatage des prix
    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice'])
        ];
    }


    public function formatPrice($number)
    {
        return number_format($number, '2', ','). ' €';
    }

    // permet dans notre fichier extension twig de créer des variables global utilisable partour dans notre environnement twig
    // pour parler avec la bdd et les champs de notre entité categorie / ici on va chercher categoryRepository qu'on a déclaré plus haut dans un construct
    public function getGlobals(): array
    {
        return [
            'allCategories' => $this->categoryRepository->findAll(),
            'fullCartQuantity' => $this->cart->fullQuantity()
        ];
    }


}