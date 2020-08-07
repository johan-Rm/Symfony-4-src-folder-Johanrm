<?php // src/Controller/DefaultController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * @Route("/email")
 */
class EmailController extends AbstractController
{
    private $mailer;
    private $translator;
    
    public function __construct(\Swift_Mailer $mailer, TranslatorInterface $translator) 
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * @Route("/confirmation-contact", name="contact-email")
     * @Method({"GET","OPTIONS"})
     * @ Method({"POST"})
     */
    public function contactEmail(Request $request): Response
    {   
        $date = date("Y");
        $mailerUser = $this->getParameter('mailer_user');
        $mailerAdmin = $this->getParameter('mailer_admin');
        $mailerDeveloper = $this->getParameter('mailer_developer');
        $logo = $this->getParameter('cdn.host') . '/uploads/media/files/logo_bg_primary_2.png';
        $params = $request->request->all();
        // dump($params);
        // $params = [
        //     'firstname' => 'johan',
        //     'lastname' => 'maurice',
        //     'to' => 'johan13.remy@gmail.com',
        //     'from' => 'contact@graines-digitales.online',
        //     'message' => 'messaaaaaage',
        //     'locale' => 'fr'
        // ];
        // die;
        $message = (new \Swift_Message('Nouvelle demande de contact'))
            ->setFrom($params['to'])
            ->setTo($params['from'])
            // ->setTo($params['from'])
            ->setCc($mailerAdmin)
            ->setCc($mailerDeveloper)
            ->setBody(
                $this->renderView(
                    'components/contact-email.html.twig'
                    , [
                        'firstname' => $params['firstname'],
                        'lastname' => $params['lastname'],
                        'email' => $params['to'],
                        'message' => $params['message'],
                        'logo' => $logo,
                        'date' => $date,
                        'reference' => (isset($params['reference'])? $params['reference']: null)
                    ]
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);

        $title = $this->translator->trans('Your message has been sent', [], 'messages', $params['locale']);
        $subTitle = $this->translator->trans('Nous avons bien pris en compte votre message et nous vous répondrons dans les meilleurs délais', [], 'messages', $params['locale']);
        $label = $this->translator->trans('Your message', [], 'messages', $params['locale']);

        $message = (new \Swift_Message($title))
            ->setFrom($params['from'])
            ->setTo($params['to'])
            ->setBody(
                $this->renderView(
                    'components/contact-confirmation-email.html.twig'
                    , [
                        'title' => $title,
                        'subTitle' => $subTitle,
                        'label' => $label,
                        'message' => $params['message'],
                        'logo' => $logo,
                        'date' => $date
                    ]
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);


        return new JsonResponse(
            'contact message ok',
            JsonResponse::HTTP_OK
        );
    }
}
