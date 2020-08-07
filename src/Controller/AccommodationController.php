<?php

namespace App\Controller;

use App\Entity\Accommodation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Filesystem\Filesystem;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Process\Process;

class AccommodationController extends AbstractController
{
    private $knpSnappy;
    private $mailer;
    private $translator;

    private $assetsPdfPath;

    public function __construct(Pdf $knpSnappy, \Swift_Mailer $mailer, TranslatorInterface $translator) 
    {
        $this->knpSnappy = $knpSnappy;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * @ Security("has_role('ROLE_ADMIN')")
     */
    protected function viewFrAction()
    {
        $id = $this->request->query->get('id');
        $accommodation = $this->em->getRepository(Accommodation::class)->findOneBy(['id' => $id]);
        $this->assetsPdfPath = $this->getParameter('assets.pdf.path');
        $filename = $accommodation->getSlug() . '.pdf';
        $pdf = $this->assetsPdfPath . '/' . $filename;
       
        return new BinaryFileResponse($pdf);
    }

    /**
     * @ Security("has_role('ROLE_ADMIN')")
     */
    protected function viewEnAction()
    {
        $id = $this->request->query->get('id');
        $accommodation = $this->em->getRepository(Accommodation::class)->findOneBy(['id' => $id]);
        $this->assetsPdfPath = $this->getParameter('assets.pdf.path');

        $translator = $this->container->get('translator');
        $name = $translator->trans('accommodation.' . $accommodation->getSlug(), [], 'accommodations-slug', 'en');
        $filename = $name . '.pdf';

        $pdf = $this->assetsPdfPath . '/' . $filename;
       
        return new BinaryFileResponse($pdf);
    }

    /**
     * @ Security("has_role('ROLE_ADMIN')")
     */
    protected function printFrAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(Accommodation::class)->findOneBy(['id' => $id]);
        $this->assetsPdfPath = $this->getParameter('assets.pdf.path');

        $filesystem = new Filesystem();
        if($filesystem->exists($this->assetsPdfPath . '/' . $entity->getSlug() . '.pdf')) {
            $filesystem->remove($this->assetsPdfPath . '/' . $entity->getSlug() . '.pdf');
        }
 
        $assetsMediaPath =  $this->container->getParameter('assets.media.folder');
        // $assetsImagesPdfPath = $this->container->getParameter('assets.pdf.path') . DIRECTORY_SEPARATOR . 'images';
        $assetsImagesPdfPath = $this->container->getParameter('assets.path') . DIRECTORY_SEPARATOR . $this->container->getParameter('assets.media.folder');

        
        $logo = $assetsImagesPdfPath . DIRECTORY_SEPARATOR . 'logo_bg_primary_2.png';
        
        foreach($entity->getGallery()->getImageGalleries() as $media) {
            $fileNameWithExtension = $media->getImage()->getFilename();
            $ext = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
            $file = basename($fileNameWithExtension);
            $filename = basename($fileNameWithExtension, ".".$ext);
            
            $sourceFilePath = $this->getParameter('assets.path') . DIRECTORY_SEPARATOR . $assetsMediaPath . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
            $targetFilePath = $assetsImagesPdfPath 
                . DIRECTORY_SEPARATOR . $filename . '.' . $ext . '.' . 'png';
           
            if($filesystem->exists($sourceFilePath) && !$filesystem->exists($targetFilePath)) {
               
                // $this->convertWebpToPng($sourceFilePath, $targetFilePath);
                // $this->convertJpegToPng($sourceFilePath, $targetFilePath);
            }               
            // die;
        }

        $host = $assetsImagesPdfPath . DIRECTORY_SEPARATOR;
        $a = $entity->getGallery()->getImageGalleries();


        $plan = false;
        foreach($entity->getPdfs() as $pdf) {
            if('plan' === $pdf->getType()->getSlug()) {
                $plan = $host . 'uploads/document/files/' . $pdf->getFilename();
            }
        }

        $html = $this->renderView(
            'components/technical-card.html.twig',
            array(
                'accommodation' => $entity,
                'logo' => $logo,
                'host' => $host,
                'plan' => $plan,
                'locale' => 'fr'
            )
        );

        $pdf = $this->assetsPdfPath . '/' . $entity->getSlug() . '.pdf';
        $this->knpSnappy->generateFromHtml(
            $html,
            $pdf
        );

        return self::viewFrAction();
    }

    /**
     * @ Security("has_role('ROLE_ADMIN')")
     */
    protected function printEnAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(Accommodation::class)->findOneBy(['id' => $id]);
        $this->assetsPdfPath = $this->getParameter('assets.pdf.path');
        
        $name = $this->translator->trans('accommodation.' . $entity->getSlug(), [], 'accommodations-slug', 'en');

        $filesystem = new Filesystem();
        if($filesystem->exists($this->assetsPdfPath . '/' . $name . '.pdf')) {
            $filesystem->remove($this->assetsPdfPath . '/' . $name . '.pdf');
        }


        $assetsMediaPath = $this->container->getParameter('assets.media.folder');
        // $assetsImagesPdfPath = $this->container->getParameter('assets.pdf.path') . DIRECTORY_SEPARATOR . 'images';
        
        $assetsImagesPdfPath = $this->container->getParameter('assets.path') . DIRECTORY_SEPARATOR . $this->container->getParameter('assets.media.folder');
        $logo = $assetsImagesPdfPath . DIRECTORY_SEPARATOR . 'logo_bg_primary_2.png';
        foreach($entity->getGallery()->getImageGalleries() as $media) {
            $fileNameWithExtension = $media->getImage()->getFilename();
            $ext = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
            $file = basename($fileNameWithExtension);
            $filename = basename($fileNameWithExtension, ".".$ext);
            
            $sourceFilePath = $this->getParameter('assets.path') . DIRECTORY_SEPARATOR .  $assetsMediaPath . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
            $targetFilePath = $assetsImagesPdfPath 
                . DIRECTORY_SEPARATOR . $filename . '.' . $ext . '.' . 'png';

            if($filesystem->exists($sourceFilePath) && !$filesystem->exists($targetFilePath)) {
                // $this->convertWebpToPng($sourceFilePath, $targetFilePath);
                // $this->convertJpegpToPng($sourceFilePath, $targetFilePath);
            }                    
        }

        $host = $assetsImagesPdfPath . DIRECTORY_SEPARATOR;

        $plan = false;
        foreach($entity->getPdfs() as $pdf) {
            if('plan' === $pdf->getType()->getSlug()) {
                $plan = $host . 'uploads/document/files/' . $pdf->getFilename();
            }
        }

        $html = $this->renderView(
            'components/technical-card.html.twig',
            array(
                'accommodation' => $entity,
                'logo' => $logo,
                'host' => $host,
                'plan' => $plan,
                'locale' => 'en'
            )
        );

        
        $pdf = $this->assetsPdfPath . '/' . $name . '.pdf';
 
        $this->knpSnappy->generateFromHtml(
            $html,
            $pdf
        );

        return self::viewEnAction();
    }

    /*
     * The method that is executed when the user performs a 'edit' action on an entity.
     *
     * @return Response|RedirectResponse
     *
     * @throws \RuntimeException
     */
    protected function editAccommodationAction()
    {
        return parent::editAction();
    }

    private function convertWebpToPng($sourceFilePath, $targetFilePath)
    {
         $cmd = [
            '/usr/bin/dwebp',
            $sourceFilePath,
            '-o',
            $targetFilePath
        ];

        $process = new Process($cmd);
        $process->setTimeout(900);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new \RuntimeException($exception->getMessage());
        }
    }

    private function convertJpegToPng($sourceFilePath, $targetFilePath)
    {
        $cmd = [
            '/usr/local/bin/convert',
            $sourceFilePath,
            $targetFilePath
        ];

        $process = new Process($cmd);
        $process->setTimeout(900);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new \RuntimeException($exception->getMessage());
        }
    }
}
