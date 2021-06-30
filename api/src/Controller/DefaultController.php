<?php

// src/Controller/DefaultController.php

namespace App\Controller;

use App\Service\MailingService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The Procces test handles any calls that have not been picked up by another test, and wel try to handle the slug based against the wrc.
 *
 * Class DefaultController
 *
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/")
     * @Template
     */
    public function indexAction(Session $session)
    {
        $variables = [];

        $session->remove('currentRequest');
        $session->remove('partners');

        return $variables;
    }

    /**
     * @Route("/getuigeninfo")
     * @Template
     */
    public function getuigenInfoAction()
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/meldinginfo")
     * @Template
     */
    public function meldingInfoAction()
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/naamsgebruik")
     * @Template
     */
    public function naamsgebruikInfoAction()
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/ambtenaar")
     * @Template
     */
    public function ambtenaarInfoAction(CommonGroundService $commonGroundService)
    {
        $variables = [];

        $organization = $commonGroundService->getResourceList(['component' => 'wrc', 'type' => 'organizations'], ['rsin' => '002220647'])['hydra:member'][0];
        $variables['products'] = $commonGroundService->getResourceList(['component' => 'pdc', 'type' => 'products'], ['groups.sourceOrganization' => $organization['@id'], 'groups.name' => 'Trouwambtenaren'])['hydra:member'];


        return $variables;
    }

    /**
     * @Route("/locaties")
     * @Template
     */
    public function locatiesInfoAction(CommonGroundService $commonGroundService)
    {
        $variables = [];

        $organization = $commonGroundService->getResourceList(['component' => 'wrc', 'type' => 'organizations'], ['rsin' => '002220647'])['hydra:member'][0];
        $variables['products'] = $commonGroundService->getResourceList(['component' => 'pdc', 'type' => 'products'], ['groups.sourceOrganization' => $organization['@id'], 'groups.name' => 'Trouwlocaties'])['hydra:member'];


        return $variables;
    }

    /**
     * @Route("/ceremonie-info")
     * @Template
     */
    public function ceremonieInfoAction()
    {
        $variables = [];

        return $variables;
    }
}
