<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class InvoiceController extends AbstractController
{

    /**
     * IMPRESSION FACTURE PDF pour un utilisateur connecté
     * Vérification de la commende pour un utilisateur donnée=
     */

    #[Route('/compte/facture/impression/{id_order}', name: 'app_invoice_customer')]
    public function printForCustomer(OrderRepository $orderRepository, $id_order): Response
    {


        // 1. Vérification de l'objet commande - Existe ?
        $order = $orderRepository->findOneById($id_order);

        if (!$order) {
            return $this->redirectToRoute('app_account');
        }

        // 1. Vérification de l'objet commande - Ok pour l'utilisateur ?

        if ($order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        // instantiate and use the dompdf class
        // à noter que notre fichier invoice/index.html.twig qui va etre converti en pdf ne prend pas en compte bootstrap
        $dompdf = new Dompdf();

        $html = $this->renderView('invoice/index.html.twig', [
            'order' => $order
        ]);

        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('facture.pdf', [
            'Attachment' => false
        ]);

        exit();
    }

    /**
     * IMPRESSION FACTURE PDF pour un administrateur connecté
     * Vérification de la commende pour un utilisateur donnée
     */

    #[Route('/admin/facture/impression/{id_order}', name: 'app_invoice_admin')]
    public function printForAdmin(OrderRepository $orderRepository, $id_order): Response
    {

        // 1. Vérification de l'objet commande - Existe ?
        $order = $orderRepository->findOneById($id_order);

        if (!$order) {
            return $this->redirectToRoute('admin');
        }

        // instantiate and use the dompdf class
        // à noter que notre fichier invoice/index.html.twig qui va etre converti en pdf ne prend pas en compte bootstrap
        $dompdf = new Dompdf();

        $html = $this->renderView('invoice/index.html.twig', [
            'order' => $order
        ]);

        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('facture.pdf', [
            'Attachment' => false
        ]);

        exit();
    }
}
