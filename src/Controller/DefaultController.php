<?php // src/Controller/DefaultController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Accommodation;
use App\Entity\WebPage;
use App\Entity\MediaObject;
use App\Entity\Message;
use App\Form\MessageType;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Contracts\Translation\TranslatorInterface;
use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message as MessageNotification;
use paragraph1\phpFCM\Recipient\Device;
use paragraph1\phpFCM\Notification;
use paragraph1\phpFCM\Recipient\Topic;


/**
 * @Route("/default")
 */
class DefaultController extends Controller
{
    private $knpSnappy;
    private $mailer;
    private $translator;

    public function __construct(Pdf $knpSnappy, \Swift_Mailer $mailer, TranslatorInterface $translator) 
    {
        $this->knpSnappy = $knpSnappy;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="webPage_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('frontend/pages/index.html.twig', []);
    }

    /**
     * @Route("/contact-confirmation-email", name="webPage_testContactConfirmationEmail", methods={"GET"})
     */
    public function contactConfirmationEmail(): Response
    {
        $date = date("Y");
        $logo = $this->getParameter('cdn.host') . '/uploads/media/files/logo_bg_primary_2.png';


        $title = $this->translator->trans('Your message has been sent', [], 'messages', 'fr');
        $subTitle = $this->translator->trans('Nous avons bien pris en compte votre message et nous vous répondrons dans les meilleurs délais', [], 'messages', 'fr');
        $label = $this->translator->trans('Your message', [], 'messages', 'fr');

        // dump('test email');die;
        return $this->render('components/contact-confirmation-email.html.twig'
            , [
                'title' => $title,
                'subTitle' => $subTitle,
                'label' => $label,
                'message' => 'mon message', 
                'logo' => $logo,
                'date' => $date
            ]
        );
    }

    /**
     * @Route("/contact-email", name="webPage_testContactEmail", methods={"GET"})
     */
    public function contactEmail(): Response
    {
        $date = date("Y");
        $logo = $this->getParameter('cdn.host') . '/uploads/media/files/logo_bg_primary_2.png';

        // dump('test email');die;
        return $this->render('components/contact-email.html.twig'
            , [ 
                'firstname' => 'remy',
                'lastname' => 'johan',
                'email' => 'johan@email.fr',
                'message' => 'mon message',
                'logo' => $logo,
                'date' => $date
            ]
        );
    }

    /**
     * @Route("/test-technical-card", name="webPage_testTechnicalCard", methods={"GET"})
     */
    public function buildPdf(): Response
    {

        $accommodation = $this->getDoctrine()
            ->getRepository(Accommodation::class)
            ->findOneById(139);

        $cdnHost = $this->container->getParameter('cdn.host');
        $logo = $cdnHost . '/uploads/media/files/logo_bg_primary_2.png';

        $plan = false;
        foreach($accommodation->getPdfs() as $pdf) {
            if('plan' === $pdf->getType()->getSlug()) {
                $plan = $cdnHost . '/uploads/document/files/' . $pdf->getFilename();
            }
        }

        $host = $cdnHost . '/uploads/media/files/';
        return $this->render('components/technical-card.html.twig', [
            'accommodation' => $accommodation,
            'logo' => $logo,
            'host' => $host,
            'plan' => $plan,
            'locale' => 'en'
        ]);
    }

    /**
     * @ Route(
     *  "/{tag}"
     *  , name="webPage_list"
     *  , methods={"GET"}
     *  , requirements={
     *      "tag": "^(?!management|contact|login|logout|api|register|resetting|form|build).[0-9a-zA-Z\-]*"
     *  }
     * )
     */
    public function list(): Response
    {
        $media = $this->getDoctrine()
            ->getRepository(MediaObject::class)
            ->findOneById(3);

        $webpages = $this->getDoctrine()
            ->getRepository(WebPage::class)
            ->findAll();

        return $this->render('frontend/pages/list.html.twig', [
            'webPages' => $webpages,
            'media' => $media,
        ]);
    }

    /**
     * @ Route(
     *  "/{tag}/{slug}"
     *  , name="webPage_show"
     *  , methods={"GET"}
     *  , defaults={"tag": "open_source", "slug": "price"}
     *  , requirements={
     *      "tag": "^(?!management|contact|login|logout|api|register|resetting|form|build).[0-9a-zA-Z\-]*",
     *      "page": "[0-9a-zA-Z\/\-]*"
     *  }
     * )
     */
    public function show(Request $request, WebPage $webpage): Response
    {

        // dump($request->attributes->all());
        // dump($webpage);
        // die();

        return $this->render('frontend/pages/show.html.twig', [
            'webPage' => $webpage,
        ]);
    }

    /**
    * @ Route(
    *   "/{tag}/{_locale}/{year}/{page}.{_format}"
    *   ,name="webPage_article"
    *   ,defaults={"_format": "html"}
    *   ,requirements={
    *       "_locale": "en|fr",
    *       "_format": "html|rss",
    *       "year": "\d+"
    *   }
    * )
    */
    public function article(WebPage $webpage): Response
    {
        // return $this->render('frontend/pages/article.html.twig', [
        //     'webPage' => $webpage,
        // ]);
    }

    /**
     * @ Route("/contact", name="webPage_contact", methods={"GET"})
     */
    public function contact(Request $request): Response
    {
        // $message = new Message();
        // $form = $this->createForm(MessageType::class, $message);
        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->persist($message);
        //     $entityManager->flush();

        //     return $this->redirectToRoute('webPage_index');
        // }

        // return $this->render('frontend/pages/contact.html.twig', [
        //     'message' => $message,
        //     'form' => $form->createView(),
        // ]);
    }

    /**
     * @Route("/notification-push-with-token", name="webPage_notificationPushWithToken", methods={"GET"})
     */
    public function notificationWithToken(Request $request): Response
    {
        $client = $this->get('moskalyovd_fcm.client');
        $now = new \DateTime();


        $data = array(
            "title" => "jjjjjjjjjjjjjjjjj"
            ,"is_background" => false
            ,"message" => "mmmmmmmmmmmmmmmmm"
            ,"image" => "https:\/\/api.androidhive.info\/images\/minion.jpg"

        );
    
        // $data = json_encode($data);

        $token = 'dk5FOCWqntU:APA91bG1b6zXbrBfpVZegIDhGZ5X0YjJOi5yzTgXh9ttp6tOMZYANj6TNyXoN_c0n-Q3CANx_1GI9DJfcCV-SA6GbWgsXYv_60uwGUlwrMbCkPxVRH5tmSlqD20BSNWdr_ERwMxegjhm';
        $token = 'd3C28FQ9JvA:APA91bG3BIZLulPCZYaN205hvtYZBBEVeDWX-u_skB6i43rcSgyu52ylvbQPnIsx3wSzCBq5DtMbGeqfHEJ2Veb19HsTVXUe_sCg5aTi7nDoqAb8wpamC4rhpC8gMLzz8KELI7RGR2y1';
        $message = new MessageNotification();
        $message->addRecipient(new Device($token));
        $message->setNotification(new Notification('The big notification', 'Youhouuuuuuu il est ' . $now->format('Y-m-d\TH:i:s'), "https:\/\/api.androidhive.info\/images\/minion.jpg" ));

        $response = $client->send($message);
        dump($response);
        die();

    }

    /**
     * @Route("/notification-push-with-topic", name="webPage_notificationPushWithTopic", methods={"GET"})
     */
    public function notificationWithtopic(Request $request): Response
    {
        $client = $this->get('moskalyovd_fcm.client');

//         $apiKey = 'AIzaSyAoxVmJx-TVSQPRRLfoVqqb9Hl1P7om_Y0';
//         $client = new Client();
//         $client->setApiKey($apiKey);
//         $client->setProxyApiUrl('https://fcm.googleapis.com/fcm/send');
//         $client->injectHttpClient(new \GuzzleHttp\Client());

        $message = new MessageNotification();
        $message->addRecipient(new Topic('com.kaizen.cms.app'));
        $notif = new Notification('test title', 'testing body');
        $notif = ['test title', 'testing body'];
        $message->setNotification($notif)
            ->setData(array('someId' => 111));
        $response = $client->send($message);
        dump($response);
        die;

    }

     /**
     * @Route("/production-build", name="webPage_productionBuild", methods={"GET"})
     */
    public function productionBuild(Request $request): Response
    {

 

        // $test = 'coucou';
        // $test = escapeshellarg($test);
        // dump($path . "/production-build-update.sh $test");
        // $output = array();
        // $output = exec($path . "/production-build-update.sh $test", $output);
        // dump($output);
        // dump('webPage_productionBuild');
        // die;

    }

}
