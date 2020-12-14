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
class WeddingController extends AbstractController
{
    /**
     * @Route("/")
     * @Template
     */
    public function indexAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/huwelijk")
     * @Template
     */
    public function huwelijkAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        $variables['processType'] = $commonGroundService->getResource(['component' => 'ptc', 'type' => 'process_types', 'id' => '5b10c1d6-7121-4be2-b479-7523f1b625f1']);
        $variables['request']['processType'] = $commonGroundService->cleanUrl(['component' => 'ptc', 'type' => 'process_types', 'id' => '5b10c1d6-7121-4be2-b479-7523f1b625f1']);
        $variables['request']['status'] = 'incomplete';
        $variables['request']['properties'] = [];
        $variables['request']['requestType'] = $commonGroundService->cleanUrl(['component' => 'vtc', 'type' => 'request_types', 'id' => '5b10c1d6-7121-4be2-b479-7523f1b625f1']);

        return $variables;
    }

    /**
     * @Route("/partner")
     * @Template
     */
    public function partnerAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/locatie")
     * @Template
     */
    public function locatieAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/ambtenaar")
     * @Template
     */
    public function ambtenaarAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        $variables['products'] = $commonGroundService->getResourceList(['component' => 'pdc', 'type' => 'products'], ['groups.id' => '7f4ff7ae-ed1b-45c9-9a73-3ed06a36b9cc'])['hydra:member'];

        return $variables;
    }

    /**
     * @Route("/datum")
     * @Template
     */
    public function datumAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/reservering")
     * @Template
     */
    public function reserveringAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/getuigen")
     * @Template
     */
    public function getuigenAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/extra")
     * @Template
     */
    public function extraAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/betalen")
     * @Template
     */
    public function betalenAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }

    /**
     * @Route("/checklist")
     * @Template
     */
    public function checklistAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        return $variables;
    }
}
