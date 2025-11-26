<?php

namespace App\Controller\Account;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressUserType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AddressController extends AbstractController
{

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/adresses', name: 'app_account_addresses')]
    public function index(): Response
    {
        return $this->render('account/address/index.html.twig');
    }

    #[Route('/compte/adresses/delete/{id}', name: 'app_account_address_delete')]
    public function delete($id, AddressRepository $addressRepository): Response
    {
        $address = $addressRepository->findOneById($id);
        if(!$address OR $address->getUser() != $this->getUser()) {
                return $this->redirectToRoute('app_account_addresses');
        }

        $this->addFlash(
                'success',
                'Votre adresse est correctement supprimé'
        );

        $this->entityManager->remove($address);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_account_addresses');
    }

    // ici dans la route on a précisé qu'il yaura un id (  {id}  ) OR dans ce cas ici
    // on veut créer une nouvelle adresse ou bien on peut modifier une adresse existante ( sur la meme page)
    // de ce fait si on veut ajouter une adresse on sera redirigé SANS id 
    // mais si on veut juste modifier une adresse alors on va ici récupérer son id
    // donc pour éviter un probleme on defaults: ['id' => null]
    // on met à nul l'id dans le nom de l'url
    // et dans une boucle if on a mit si l'id existe alors récupere l'id dans la bdd
    // sinon si pas d'id existant alors on appelle nouvelle objet Adress à laquelle on set l'utilisateur connecté
    #[Route('/compte/adresse/ajouter/{id}', name: 'app_account_address_form', defaults: ['id' => null] )]
    public function form(Request $request, $id, AddressRepository $addressRepository, Cart $cart): Response
    {
        if ($id){
            $address = $addressRepository->findOneById($id);
            if(!$address OR $address->getUser() != $this->getUser()) {
                return $this->redirectToRoute('app_account_addresses');
            }
        } else {
            $address = new Address();
            $address->setUser($this->getUser());
        }

        $form = $this->createForm(AddressUserType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            
            $this->addFlash(
                'success',
                'Votre adresse est correctement sauvegardé'
            );
            
            if ($cart->fullQuantity() > 0) {
                return $this->redirectToRoute('app_order');
            }
            
            return $this->redirectToRoute('app_account_addresses');
        }

        return $this->render('account/address/form.html.twig', [
            'addressForm' => $form
        ]);
    }

    
}

?>