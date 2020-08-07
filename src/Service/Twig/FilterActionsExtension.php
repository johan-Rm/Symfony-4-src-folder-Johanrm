<?php
namespace App\Service\Twig;

use HelperBundle\Tool\Shortcuts as Sf;
use HelperBundle\Tool\Store;

class FilterActionsExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'filter_admin_actions',
                [$this, 'filterActions']
            )
        ];
    }

    public function filterActions(array $itemActions, $item)
    {
        if (method_exists($item, 'filterActions')) {
            $itemActions = $item->filterActions($itemActions, $item);
        }
        return array_diff_key($itemActions, array_flip(
            $GLOBALS['kernel']->getContainer()->getParameter('global_actions')
        ));
    }
}
