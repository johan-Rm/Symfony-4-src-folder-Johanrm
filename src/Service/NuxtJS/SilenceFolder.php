<?php

namespace App\Service\NuxtJS;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;



class SilenceFolder
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
    * 
    **/
    public function generate($folderPath) 
    {
        $filesystem = new Filesystem();
        $content = "<?php" . PHP_EOL . "// silence is golden";

        $finder = new Finder();
        $finder->directories()->in($folderPath);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $absoluteFilePath = $file->getRealPath();
                $filename =  $absoluteFilePath . DIRECTORY_SEPARATOR . 'index.php';
                $filesystem->dumpFile($filename, $content);
                dump($filename);
            }
        }

        $filename =  $folderPath . DIRECTORY_SEPARATOR . 'index.php';
        $filesystem->dumpFile($filename, $content);
        dump($filename);
    }

}
