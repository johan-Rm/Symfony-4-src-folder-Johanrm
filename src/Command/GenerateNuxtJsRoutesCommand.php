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
use Symfony\Component\Console\Helper\Table;


class GenerateNuxtJsRoutesCommand extends Command
{
    use LockableTrait;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:generate-nuxtjs-routes';

	private $container;

    private $mailer;

    private $templating;

    private $rootBuild = true;

    private $isWriteLn = false;

    public function __construct(\Swift_Mailer $mailer, ContainerInterface $container, Environment $templating)
    {
        $this->container = $container;

        $this->mailer = $mailer;

        $this->templating = $templating;

        parent::__construct();
    }

    protected function configure()
    {
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Generate new NuxtJS routes.')
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to generate new NuxtJS routes...')
        ->addArgument('full', InputArgument::OPTIONAL, 'Full build ?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailerUser = $this->container->getParameter('mailer_user');
        $mailerAdmin = $this->container->getParameter('mailer_admin');
        $mailerDeveloper = $this->container->getParameter('mailer_developer');
        $nuxtProjectDir = $this->container->getParameter('view.project_dir');
        
        $exportApiToJson = $this->container->get('app.export.api_to_json');
        
        $viewHost = $this->container->getParameter('view.host');
        $sitewebTitle = $this->container->getParameter('siteweb.title');
       
        $waitingFolder = $this->container->getParameter('view.build_routes.path') . '/waiting/';
        $processFolder = $this->container->getParameter('view.build_routes.path') . '/process/';
        $shortLogs = [];
        $logs = [];
        $start = date('Y-m-d\ H:i:s.u');
        $this->writeln($output, 'start to => ' . $start);
        $logs[] = $start;
        
        $success = false;
        if ($this->lock()) {
            $full = $input->getArgument('full');
            if ('full' === $full) {

                $successTitle = 'Nouvelle mise à jour des données complètes';
                $status = false;
                // Execution de la commande de génération de toutes les routes
                $command = $nuxtProjectDir . "/synchronization/production-build-full-with-one-command.sh";
                $process = new Process(
                    [ $command, $nuxtProjectDir ]
                );
                $process->setTimeout(10800); // 3 heures

                /** logs **/
                $this->writeln($output, $command);
                $logs[] = 'run command full';
                $logs[] = $command;

                $shortLogs[] = $viewHost . '/';

                 $filesystem = new Filesystem();
                if(!$filesystem->exists($this->container->getParameter('view.build_routes.path'))) {
                    $filesystem->mkdir($this->container->getParameter('view.build_routes.path'));
                    $filesystem->mkdir($this->container->getParameter('view.build_routes.path') . '/process/');
                    $filesystem->mkdir($this->container->getParameter('view.build_routes.path') . '/waiting/');
                }
                try {
                    $process->mustRun();
                    /** logs **/
                    $this->writeln($output, $process->getOutput());
                    $logs[] = 'success';
                    $logs[] = $process->getOutput();
                    $status = true;

                } catch (ProcessFailedException $exception) {
                   /** logs **/
                    $this->writeln($output, $process->getOutput());
                    $logs[] = 'error';
                    $logs[] = $exception->getMessage();
                }

                // nettoyage complet des dossiers contenant les json de mise à jour des datas
                if(true === $status) {
                    $finder = new Finder();
                    $finder->files()->in($waitingFolder);
                    if ($finder->hasResults()) {
                        foreach ($finder as $file) {
                            $absoluteFilePath = $file->getRealPath();
                            $fileNameWithExtension = $file->getRelativePathname();
                            $filesystem->remove($absoluteFilePath);
                        }
                    }
                    
                    $finder = new Finder();
                    $finder->files()->in($processFolder);
                    if ($finder->hasResults()) {
                        foreach ($finder as $file) {
                            $absoluteFilePath = $file->getRealPath();
                            $fileNameWithExtension = $file->getRelativePathname();
                            $filesystem->remove($absoluteFilePath);
                        }
                    }

                    $success = true;
                }
            } else {

                $successTitle = 'Nouvelle mise à jour des données';
                $status = false;
                $finder = new Finder();
                // find all files in the current directory
                $finder->files()->in($waitingFolder);
                // check if there are any search results
                if ($finder->hasResults()) {

                    if(true === $this->rootBuild) {

                        $command = $nuxtProjectDir . "/synchronization/production-build-root.sh";
                        $process = new Process(
                            [ $command, $nuxtProjectDir ]
                        );
                        $process->setTimeout(900);

                        /** logs **/
                        $this->writeln($output, $command);
                        $logs[] = 'run command root';
                        $logs[] = $command;

                        $filesystem = new Filesystem();
                        try {

                            $process->mustRun();

                            /** logs **/
                            $this->writeln($output, $process->getOutput());
                            $logs[] = 'success';
                            $logs[] = $process->getOutput();
                            $status = true;

                        } catch (ProcessFailedException $exception) {

                            foreach ($finder as $file) {
                                $absoluteFilePath = $file->getRealPath();
                                $fileNameWithExtension = $file->getRelativePathname();
                                $filesystem->remove($absoluteFilePath);
                            }

                            /** logs **/
                            $this->writeln($output, $process->getOutput());
                            $logs[] = 'error';
                            $logs[] = $exception->getMessage();

                        }
                    } else {
                        $status = true;
                    }

                    if(true === $status) {
                        foreach ($finder as $file) {

                            $absoluteFilePath = $file->getRealPath();
                            $this->writeln($output, '<comment>absoluteFilePath => ' . $absoluteFilePath . '</comment>');

                            $start = date('Y-m-d\ H:i:s.u');
                            $this->writeln($output, '<info>build single START to => ' . $start . '</info>');

                            $fileNameWithExtension = $file->getRelativePathname();
                            $routeConfig = json_decode($file->getContents(), true);
                            
                            /** création des json datas **/
                            $options = [
                                'query' => [
                                    'slug' => $routeConfig['slug']
                                ]
                            ];
                            $exportApiToJson->generateOne($routeConfig['route'], $options);

                            $this->writeln($output, '<info>json datas => ' . $routeConfig['route'] . '</info>');
                            

                            $optionsList = [
                                'query' => [
                                    'slug' => null,
                                    'pagination' => false
                                ]
                            ];
                            $exportApiToJson->generateList($routeConfig['route'], $options);


                            $filesystem = new Filesystem();
                            $filesystem->mkdir($processFolder);
                            if($filesystem->exists(
                                    $processFolder . $fileNameWithExtension
                                )
                            ){
                                $filesystem->remove(
                                    $processFolder . $fileNameWithExtension
                                );
                            }
                            $filesystem->rename(
                                $absoluteFilePath
                                , $processFolder . $fileNameWithExtension
                            );
                            $shortLogs[] = $viewHost . $routeConfig['baseUrl'] . $routeConfig['slug'];
            				$command = $routeConfig['pathFile'] . "/synchronization/production-build-single.sh";
            	            $process = new Process(
            	            	[
            	            		$command
            	            		, $routeConfig['pathFile']
            	            		, $routeConfig['entity']
            	            		, $routeConfig['path']
            	            		, $routeConfig['route']
            	            		, $routeConfig['slug']
            	            	]
            	            );
            	            $process->setTimeout(900);

                            /** logs **/
                            $this->writeln($output, $command);
                            $this->writeln($output, $routeConfig);
                            $logs[] = 'run command single';
                            $logs[] = $command;

            				try {
                                if(!$this->isWriteLn) {
                                    $process->mustRun();
                                } else {
                                    $process->mustRun(function ($type, $buffer) {
                                        if (Process::ERR === $type) {
                                            echo 'ERR > '.$buffer;
                                            
                                        } else {
                                            echo 'OUT > '.$buffer;
                                            
                                        }
                                    });
                                }
            				    

                                /** logs **/
                                // $this->writeln($output, $process->getOutput());
                                $logs[] = 'success';
                                $logs[] = $process->getOutput();
                                $logs[] = $processFolder . $fileNameWithExtension;

                               
            				} catch (ProcessFailedException $exception) {
            					/** logs **/
                                $this->writeln($output, $process->getOutput());
                                $logs[] = 'error';
                                $logs[] = $exception->getMessage();
            				}

                            $filesystem->remove(
                                $processFolder . $fileNameWithExtension
                            );
                            
                            $start = date('Y-m-d\ H:i:s.u');
                            $this->writeln($output, '<info>build single END to => ' . $start . '</info>');
                        }

                        $success = true;

                    } else {
                        $message = 'the process root are failed.';
                        $logs[] = $message;
                        $this->writeln($output, $message);
                    }

                   

                    $logsText = implode("\r\n", $logs);
                    $filesystem = new Filesystem();
                    $filesystem->dumpFile(
                        'log_info_generate_single_route.txt'
                        , $logsText
                    );

                    $logo = $this->container->getParameter('cdn.host') . '/uploads/media/files/logo_bg_primary_2.png';
                    $message = (new \Swift_Message($sitewebTitle . ' - nouvelle mise à jour des données'))
                        ->setFrom($mailerUser)
                        // ->addTo($mailerAdmin[0])
                        // ->addTo($mailerAdmin[1])
                        ->setTo($mailerAdmin)
                        ->setCc($mailerDeveloper)
                        // ->setTo($params['from'])
                        ->setBody(
                            $this->templating->render(
                                'components/production-build-email.html.twig'
                                , [
                                    'successTitle' => $successTitle,
                                    'logs' => $shortLogs,
                                    'logo' => $logo,
                                    'success' => $success,
                                    'viewHost' => $viewHost,
                                    'mailerAdmin' => $mailerAdmin
                                ]
                            ),
                            'text/html'
                        )
                        ->attach(\Swift_Attachment::fromPath(
                            $this->container
                            ->get('kernel')
                            ->getProjectDir() . '/log_info_generate_single_route.txt'
                        ))
                        
                    ;
                    $this->mailer->send($message);
                    $this->writeln($output, 'seeeeeeend');
                } else {
                    $message = 'there are no files to update.';
                    $logs[] = $message;
                    $this->writeln($output, $message);
                }
            }

        } else {
            $message = 'The command is already running in another process.';
            $this->writeln($output, $message);
        }
        
       
        $end = 'end to =>  ' . date('Y-m-d\ H:i:s.u');
        $logs[] = $end;
        $this->writeln($output, $end);

        // If you prefer to wait until the lock is released, use this:
        // $this->lock(null, true);
        // ...
        // if not released explicitly, Symfony releases the lock
        // automatically when the execution of the command ends
        $this->release();

        return 0;
    }

    private function writeln($output, $message)
    {
        if(true === $this->isWriteLn) {
            if(!is_array($message)) {
                $output->writeln($message);
            } else {
      
                $array = [];
                foreach(array_keys($message) as $key=>$value) {
                    $array[] = [$value, $message[$value]];
                }
                $table = new Table($output);
                $table
                    ->setHeaders(['Key', 'Value'])
                    ->setRows($array)
                ;
                $table->render();
            }
        }

        return true;
    }
}