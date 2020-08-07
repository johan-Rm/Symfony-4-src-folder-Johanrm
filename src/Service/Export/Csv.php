<?php

namespace App\Service\Export;

use App\Entity\Accommodation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Csv
{
    public function getResponseFromQueryBuilder(QueryBuilder $queryBuilder, $columns, $params, $filename)
    {
        $entities = new ArrayCollection($queryBuilder->getQuery()->getResult());
        $response = new StreamedResponse();

        if (is_string($columns)) {
            $columns = $this->getColumnsForEntity($columns);
        }

        $response->setCallback(function () use ($entities, $columns) {
            $handle = fopen('php://output', 'w+');

            // Add header
            fputcsv($handle, array_keys($columns));

            while ($entity = $entities->current()) {
                $values = [];

                foreach ($columns as $column => $callback) {
                    $value = $callback;

                    if (is_callable($callback)) {
                        $value = $callback($entity);
                    }

                    $values[] = $value;
                }

                fputcsv($handle, $values);

                $entities->next();
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    private function getColumnsForEntity($class)
    {
        // $columns[$class] = [
        //     'reference' => function (Accommodation $accommodation) {
        //         return $accommodation->getReference();
        //     },
        //     'price' => function (Accommodation $accommodation) {
        //         return $accommodation->getPrice();
        //     }
        // ];
        // dump($columns);
        // die;

        if (array_key_exists($class, $columns)) {
            return $columns[$class];
        }

        throw new \InvalidArgumentException(sprintf(
            'No columns set for "%s" entity',
            $class
        ));
    }
}
