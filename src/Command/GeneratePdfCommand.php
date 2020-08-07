<?php

namespace App\Command;

use Twig\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\Console\Input\InputOption;
use Knp\Snappy\Pdf;
use App\Entity\Accommodation;


class GeneratePdfCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-pdf';

	private $container;

    private $em;

    private $translator;

    private $templating;

    private $assetsPdfPath;

    public function __construct(ContainerInterface $container, Environment $templating, Pdf $knpSnappy)
    {
        $this->container = $container;
        $this->templating = $templating;
        $this->em = $this->container->get('doctrine')->getEntityManager();
        $this->knpSnappy = $knpSnappy;
        $this->assetsPdfPath = $this->container->getParameter('assets.pdf.path');
        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Generate all pdfs.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate all pdfs...')
        // ->addOption('no-write', null, InputOption::VALUE_OPTIONAL, 'No write ?', false)
        // ->addOption('write-only', null, InputOption::VALUE_OPTIONAL, 'Write only ?', false)
        ; 
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->lock()) {
            $start = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);

            $filesystem = new Filesystem();
            $assetsImagesPdfPath = $this->assetsPdfPath . DIRECTORY_SEPARATOR . 'images';
            if(!$filesystem->exists($assetsImagesPdfPath)) {
                          
                $filesystem->mkdir($assetsImagesPdfPath);
            }

            $assetsPath = $this->container->getParameter('assets.path');
            $assetsMediaPath = $this->container->getParameter('assets.media.folder');
            $assetsMediaPath = $assetsPath . DIRECTORY_SEPARATOR . $assetsMediaPath;
            $filename = 'logo_footer.jpg';
            $sourceFilePath = $assetsMediaPath . DIRECTORY_SEPARATOR . $filename;
            $targetFilePath = $assetsImagesPdfPath . DIRECTORY_SEPARATOR . $filename . '.' . 'png';
            if($filesystem->exists($sourceFilePath)) {
                  
                $this->convertWebpToPng($sourceFilePath, $targetFilePath);
                
            }

            $results = $this->em->getRepository(Accommodation::class)->findAll();
            foreach ($results as $key => $entity) {
                foreach($entity->getGallery()->getImageGalleries() as $media) {
                    $fileNameWithExtension = $media->getImage()->getFilename();
                    $ext = pathinfo($fileNameWithExtension, PATHINFO_EXTENSION);
                    $file = basename($fileNameWithExtension);
                    $filename = basename($fileNameWithExtension, ".".$ext);
                    
                    $sourceFilePath = $assetsMediaPath . DIRECTORY_SEPARATOR . $filename . '.' . $ext;
                    $targetFilePath = $assetsImagesPdfPath 
                        . DIRECTORY_SEPARATOR . $filename . '.' . $ext . '.' . 'png';

                    if($filesystem->exists($sourceFilePath) && !$filesystem->exists($targetFilePath)) {
                        $this->convertWebpToPng($sourceFilePath, $targetFilePath);
                    }                        
                }

                $filename = $this->createPdf($entity, $assetsImagesPdfPath);
                $output->writeln($filename);
                // break;
            }
            $end = date('Y-m-d\ H:i:s.u');
            $output->writeln('start to => ' . $start);
            $output->writeln('end to => ' . $end);
        }
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

    private function createPdf($entity, $assetsImagesPdfPath)
    {
        $this->knpSnappy->setTimeout(300);
        

        $filesystem = new Filesystem();
        if($filesystem->exists($this->assetsPdfPath . DIRECTORY_SEPARATOR . $entity->getSlug() . '.pdf')
        ) {
            $filesystem->remove($this->assetsPdfPath . DIRECTORY_SEPARATOR  . $entity->getSlug() . '.pdf');
        }
        
        $host = $assetsImagesPdfPath . DIRECTORY_SEPARATOR;
        $logo = $host . DIRECTORY_SEPARATOR  . 'logo_footer.jpg';
        
        $plan = false;
        foreach($entity->getPdfs() as $pdf) {
            if('plan' === $pdf->getType()->getSlug()) {
                $plan = $this->container->getParameter('cdn.host') . DIRECTORY_SEPARATOR . $this->container->getParameter('assets.media.folder') . DIRECTORY_SEPARATOR . 'uploads/document/files/' . $pdf->getFilename();
            }
        }

        $html = $this->templating->render(
            'components/technical-card.html.twig',
            array(
                'accommodation' => $entity,
                'logo' => $logo,
                'host' => $host,
                'plan' => $plan
            )
        );
        
        $filename = $this->assetsPdfPath . DIRECTORY_SEPARATOR  . $entity->getSlug() . '.pdf';
        $this->knpSnappy->generateFromHtml(
            $html,
            $filename
        );

        return $filename;
    }

}