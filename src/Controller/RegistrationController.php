<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Form\RegistrationType;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\ProductsRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/registration", name="registration")
     */
    public function registrer(Request $request, \Swift_Mailer $mailer): Response
    {
        $form = $this->createForm(RegistrationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registration = $form->getData();
            $message = (new \Swift_Message('Validation Commande'))
                ->setFrom('masmoudi.emna@esprit.tn')
                ->setTo($registration['email'])
                ->setBody(
                    $this->renderView(
                        'reg/val.html.twig', compact('registration')
                    ),
                    'text/html'
                );

            // On envoie le message

            $mailer->send($message);

            $this->addFlash('message', 'Le message a bien été envoyé');
            return $this->redirectToRoute('product_index');
        }

        return $this->render('reg/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/imprimer", name="imprimer")
     */
    public function imprimer(LigneCommandeRepository $Repository)
    {
        //$commande=$commandeRepository->find($id);

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('commande/imprime.html.twig', [
            'commandes' => $Repository->findAll()
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("myOrder.pdf", [
            "Attachment" => false
        ]);
    }


}

