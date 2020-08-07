<?php

namespace App\Service\Billing;

use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;
use App\Entity\Invoice;

class Numerator
{
    /** @var ConfigManager */
    private $config;
    private $em;
    private $token;
    private $translator;

    public function __construct(EntityManager $em, ConfigManager $config, TokenStorage $token, TranslatorInterface $translator)
    {
        $this->config = $config;
        $this->em = $em;
        $this->token = $token;
        $this->translator = $translator;
    }

    public function incrementNumber(Invoice $invoice)
    {
        $lastInvoice = $this->em->getRepository(Invoice::class)->findLastByCompany($invoice->getUserCreated()->getCompany());
        $reference = 0;
        if(is_callable([$lastInvoice, 'getReference']) &&  null !== $lastInvoice->getReference()) {
            $reference = (int)$lastInvoice->getReference();
        }
        $reference = $reference + 1;
        $reference = sprintf("%07d", $reference);
        $invoice->setReference($reference);

        return $invoice;
    }


}
