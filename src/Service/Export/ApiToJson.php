<?php

namespace App\Service\Export;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\Common\Util\Inflector;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use App\Entity\Article;
use App\Entity\WebPage;
use App\Entity\Accommodation;
use App\Entity\AccommodationType;

class ApiToJson
{
    private $container;
    private $em;
    private $viewDataPath;
    private $filesystem;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->viewDataPath = $this->container->getParameter('view.data.path');
        $this->filesystem = new Filesystem();
    }

    public function generateList($entity, $options = [])
    {   
        switch ($entity) {
            case 'web_pages':
                $webPages = $this->em->getRepository(WebPage::class)
                    ->findBy(
                        array('isActive' => true)
                    )
                ;
                if(count($webPages) > 0) {
                    $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . $entity;
                    $this->createFolder($viewDataPath);
                    foreach ($webPages as $key => $value) {
                        $slug = $value->getSlug();
                        // dump($slug);            
                        $options = [
                            'query' => [
                                'slug' => $slug,
                                'pagination' => false
                            ]
                        ];
                        $this->generateJson($entity, $options, $viewDataPath);
                    }    
                }              
                // dump('count : ' . count($webPages));
                
                break;
            case 'full_articles':
                $viewDataPathRoot = $this->viewDataPath . DIRECTORY_SEPARATOR . $entity;
                $this->createFolder($viewDataPathRoot);
                $entity = 'articles';
                $articles = $this->em->getRepository(Article::class)
                    ->findBy(
                        array('isActive' => true)
                    )
                ;
                if(count($articles) > 0) {
                    $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . $entity;
                    $this->createFolder($viewDataPath);
                    foreach ($articles as $key => $value) {
                        $slug = $value->getSlug();
                        // dump($slug);            
                        $options = [
                            'query' => [
                                'slug' => $slug,
                                'pagination' => false
                            ]
                        ];
                        // $this->generateJson($entity, $options, $viewDataPathRoot);
                        
                        $this->generateJson($entity, $options, $viewDataPath);
                    }    
                }              
                // dump('count : ' . count($articles));

                break;
            case 'full_accommodations':

                // $viewDataPathRoot = $this->viewDataPath . DIRECTORY_SEPARATOR . $entity;
                // $this->createFolder($viewDataPathRoot);

                $entity = 'accommodations';
                $accommodations = $this->em->getRepository(Accommodation::class)
                    ->findBy(
                        array('isActive' => true)
                    )
                ;
                if(count($accommodations) > 0) {
                    $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . $entity;
                    $this->createFolder($viewDataPath);
                    foreach ($accommodations as $key => $value) {
                        $slug = $value->getSlug();
                        $category = $value->getType()->getSlug();
                        $options = [
                            'query' => [
                                'slug' => $slug,
                                'pagination' => false
                            ]
                        ];
                        // $this->generateJson($entity, $options, $viewDataPathRoot);
                        // $options['query']['category'] = $category;
                        $this->generateJson($entity, $options, $viewDataPath);
                    }
                }              
                // dump('count accommodations : ' . count($accommodations));

                
                // Vente
                $accommodation_types = $this->em->getRepository(AccommodationType::class)
                    ->findBy(array('isActive' => true))
                ;
                if(count($accommodation_types) > 0) {
                    $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . 'accommodations'  . DIRECTORY_SEPARATOR . 'vente';
                    $this->createFolder($viewDataPath);
                    foreach ($accommodation_types as $key => $value) {
                        $slug = $value->getSlug();
                        $options = [
                            'specific_query' => [
                                'nature.slug' => 'vente',
                                'type.slug' => $slug,
                                'itemsPerPage' => 10,
                                'isActive' => true,
                                'pagination' => false
                            ],
                            'query' => [
                                'slug' => $slug,
                                'pagination' => false
                            ]
                        ];
                        
                        $this->generateJson($entity, $options, $viewDataPath);
                    }
                }
                $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . 'accommodations';
                $options = [
                    'specific_query' => [
                        'nature.slug' => 'vente',
                        'itemsPerPage' => 10,
                        'isActive' => true,
                        'pagination' => false
                    ],
                    'query' => [
                        'slug' => 'vente',
                        'pagination' => false
                    ]
                ]; 
                $this->generateJson($entity, $options, $viewDataPath);      
                // dump('count accommodation_types vente: ' . count($accommodation_types));

                 // Location
                $accommodation_types = $this->em->getRepository(AccommodationType::class)
                    ->findBy(array('isActive' => true))
                ;
                if(count($accommodation_types) > 0) {
                    $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . 'accommodations'  . DIRECTORY_SEPARATOR . 'location';
                    $this->createFolder($viewDataPath);
                    foreach ($accommodation_types as $key => $value) {
                        $slug = $value->getSlug();
                        $options = [
                            'specific_query' => [
                                'nature.slug' => 'location',
                                'type.slug' => $slug,
                                'itemsPerPage' => 10,
                                'isActive' => true,
                                'pagination' => false
                            ],
                            'query' => [
                                'slug' => $slug,
                                'pagination' => false
                            ]
                        ];
                        
                        $this->generateJson($entity, $options, $viewDataPath);
                    }
                }
                $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . 'accommodations';
                $options = [
                    'specific_query' => [
                        'nature.slug' => 'location',
                        'itemsPerPage' => 10,
                        'isActive' => true,
                        'pagination' => false
                    ],
                    'query' => [
                        'slug' => 'location',
                        'pagination' => false
                    ]
                ]; 
                $this->generateJson($entity, $options, $viewDataPath);

                // dump('count accommodation_types location: ' . count($accommodation_types));
                
                break;
        }

        return true;
    }

    public function generateOne($entity, $options = [])
    {
        
        $items = explode("_", $entity);
        if($items[0] == "full") {
            $entity = $items[1];
        }
        $viewDataPath = $this->viewDataPath . DIRECTORY_SEPARATOR . $entity;

        $this->createFolder($viewDataPath);
        $this->generateJson($entity, $options, $viewDataPath);
    }

    private function createFolder($path)
    {
        if(!$this->filesystem->exists($path)) {
            $this->filesystem->mkdir($path);
        }
    }

    private function generateJson($entity, $options = [], $viewDataPath)
    {
        $json = $this->getJson($entity, $options);
        if(false !== $json) {
            if(isset($options['query']['category'])) {
                $viewDataPath = $viewDataPath . DIRECTORY_SEPARATOR . $options['query']['category'];
                $this->createFolder($viewDataPath);
            }
            $filename = $options['query']['slug'] . '.json';
            // dump($viewDataPath . DIRECTORY_SEPARATOR . $filename);die;
            $this->filesystem->dumpFile(
                $viewDataPath . DIRECTORY_SEPARATOR . $filename
                , $json
            ); 
        }
    }

    private function getJson($entity, $options = []) 
    {
        $apiHost = $this->container->getParameter('api.host');
        $client = HttpClient::create();
        if(isset($options['specific_query'])) {
            $response = $client->request(
                'GET'
                , $apiHost . DIRECTORY_SEPARATOR . $entity 
                , [
                    'query' => $options['specific_query']
                ]
            );

        } else {
            $response = $client->request(
                'GET'
                , $apiHost . DIRECTORY_SEPARATOR . $entity . DIRECTORY_SEPARATOR . $options['query']['slug']
                , [
                    'query' => [
                        'isActive' => true,
                        'pagination' => false
                    ]
                ]
            );
        }
        
        // $response = $client->request('GET', $apiHost . DIRECTORY_SEPARATOR . $entity, $options);
        if(200 === $response->getStatusCode()) {
            $content = $response->getContent();
            $content = json_decode($content);
            $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
            
            return $content;    
        }

        return false;
    }

    public function generate($entity, $options = [])
    {

   		$viewDataPath = $this->container->getParameter('view.data.path');
        $filesystem = new Filesystem();
        if(!$filesystem->exists($this->viewDataPath)) {
            $filesystem->mkdir($this->viewDataPath);
        }

        $apiHost = $this->container->getParameter('api.host');

 
        $client = new CurlHttpClient();
        // $client = CurlHttpClient::create(['http_version' => '2.0']);
        $url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id=ChIJQc8hbUiarQ0R4H-Uc3WW1OY&key=AIzaSyCVeIkJL0QqHjiBNyQIyrFgAD758VeSkBs&fields=reviews&language=fr';

        $response = $client->request('GET', $url);
        $statusCode = $response->getStatusCode();
        if(200 === $statusCode) {
            $contentType = $response->getHeaders()['content-type'][0];
            // dump($response);die;
            $content = $response->getContent();
            $content = json_decode($content);
            $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
            $filesystem = new Filesystem();
            $filesystem->dumpFile(
                    $viewDataPath . '/testimonials.json'
                    , $content
            );
        }
       
    
        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/organizations/1');
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/organization.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/organizations', [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'type.slug' => 'lien-reseau-social'
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/organization-lien-reseau-social.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/real_estate_agents/2');
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/real-estate-agent.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/real_estate_agents');
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/real-estate-agents.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/web_pages', [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'isActive' => true
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/web_pages.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/full_articles', [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'isActive' => true
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/articles.json'
            , $content
        ); 

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/full_accommodations', [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'isActive' => true
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/accommodations.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/accommodation_types', [
            'query' => [
                'isActive' => true
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $viewDataPathLocation = $viewDataPath . DIRECTORY_SEPARATOR . 'accommodation_types';
        $this->createFolder($viewDataPathLocation);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPathLocation . '/location.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/accommodation_types', [
            'query' => [
                'isActive' => true,
                'isLocation'=> false
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $viewDataPathVente = $viewDataPath . DIRECTORY_SEPARATOR . 'accommodation_types';
        $this->createFolder($viewDataPathVente);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPathVente . '/vente.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/accommodation_locations', [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'isActive' => true
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/accommodation_locations.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/tags', [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'isActive' => true
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/tags.json'
            , $content
        );

        $client = HttpClient::create();
        $response = $client->request('GET', $apiHost . '/components', [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'type' => 'bloc'
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = json_decode($content);
        $content = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |  JSON_UNESCAPED_UNICODE);
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $viewDataPath . '/components.json'
            , $content
        );
    }

}
