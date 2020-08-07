<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use App\Entity\MediaObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Cocur\Slugify\Slugify;
use Symfony\Component\Filesystem\Filesystem;


class AdminController extends AbstractController
{

    /**
     * @Route("/phpinfo", name="easyadmin_phpinfo")
     */
    public function phpInfoAction(): Response
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }
        ob_start();
        phpinfo();
        $str = ob_get_contents();
        ob_get_clean();

        return new Response($str);
    }

    /**
     * Allows applications to modify the entity associated with the item being
     * created while persisting it.
     *
     * @param object $entity
     */
    protected function updateTranslationEntity($entity)
    {
        if(false == $entity->getIsHtml()) {
            $value = strip_tags($entity->getValueRight());
            $entity->setValueRight($value);
        }
        
        $this->em->flush();

        $translator = $this->container->get('app.translator');  
        $translator->write();
    }

    /**
     * The method that is executed when the user performs a 'list' action on an entity.
     *
     * @return Response
     */
    protected function listMediaAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_LIST);

        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->entity['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'), $this->entity['list']['dql_filter']);

        $this->dispatch(EasyAdminEvents::POST_LIST, ['paginator' => $paginator]);

        $parameters = [
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
        ];

        return $this->executeDynamicMethod('render<EntityName>Template', ['list', 'pages/list.html.twig', $parameters]);
    }

    /**
    * @Route("/dashboard", name="dashboard")
    *
    * @return \Symfony\Component\HttpFoundation\Response
    */
    public function dashboardAction(Request $request)
    {
        $parameters['headline'] = 'Dashboard Coming Soon';

        return $this->render('pages/dashboard.html.twig', $parameters);
    }

    /**
     * @Route("/coming_soon", name="coming_soon")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws ForbiddenActionException
     */
    public function comingSoonAction(Request $request)
    {
       $parameters['headline'] = 'Coming soon';
       $parameters['about'] = 'The false text is, in print, a text without meaning, whose sole purpose is to calibrate the content ...';

       return $this->render('pages/coming_soon.html.twig', $parameters);
    }

    public function downloadAction()
    {
        throw new \RuntimeException('Action for download an entity not defined');
    }

    /**
    *
    * @ Method({"GET", "POST"})
    * @Route("/ajax/media/download", name="ajax_media_download")
    */
   public function ajaxMediaDownloadAction(Request $request)
   {

        $em = $this->container->get("doctrine.orm.default_entity_manager");
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');

        $filename = pathinfo($uploadedFile->getClientOriginalName(),  PATHINFO_FILENAME);
        $assetsMediaPath = $this->container->getParameter('assets.path') . DIRECTORY_SEPARATOR . $this->container->getParameter('assets.media.folder');

        $filesystem = new Filesystem();
        if(!$filesystem->exists($assetsMediaPath . DIRECTORY_SEPARATOR . $filename . '.jpg')) {
            if(null !== $uploadedFile) {
                $media = new MediaObject();
                $media->setName($uploadedFile->getClientOriginalName());
                $media->setUrl($uploadedFile->getPath());
                $media->setDimensions([1920,1281]);
                $media->setOriginalFilename($uploadedFile->getClientOriginalName());
                $media->setFilename($uploadedFile->getClientOriginalName());
                $media->setEncodingFormat($uploadedFile->getMimeType());
                $media->setContentSize($uploadedFile->getSize());
                $media->setFile($uploadedFile);
                $em->persist($media);
                $em->flush();

                return new JsonResponse(array('success' => true));
            }
        }

        return new JsonResponse(array('success' => false));
   }

}
