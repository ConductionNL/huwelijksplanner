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

        if ($session->get('currentRequest')) {
            $variables['request'] = $session->get('currentRequest');
        } else {
            $variables['processType'] = $commonGroundService->getResource(['component' => 'ptc', 'type' => 'process_types', 'id' => '5b10c1d6-7121-4be2-b479-7523f1b625f1']);
            $variables['request']['processType'] = $commonGroundService->cleanUrl(['component' => 'ptc', 'type' => 'process_types', 'id' => '5b10c1d6-7121-4be2-b479-7523f1b625f1']);
            $variables['request']['status'] = 'incomplete';
            $variables['request']['submitters'][0]['brp'] = $this->getUser()->getPerson();
            $variables['request']['properties'] = [];
            $variables['request']['organization'] = $commonGroundService->cleanUrl(['component' => 'wrc', 'type' => 'organizations', 'id' => '68b64145-0740-46df-a65a-9d3259c2fec8']);
            $variables['request']['requestType'] = $commonGroundService->cleanUrl(['component' => 'vtc', 'type' => 'request_types', 'id' => '5b10c1d6-7121-4be2-b479-7523f1b625f1']);

            $session->set('currentRequest', $variables['request']);
        }

        if ($request->isMethod('POST')) {

            $currentRequest = $session->get('currentRequest');
            $currentRequest['properties']['type'] = $request->get('type');
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);

            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_partner'));
        }

        return $variables;
    }

    /**
     * @Route("/partner")
     * @Template
     */
    public function partnerAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        $variables['request'] = $session->get('currentRequest');

        if (isset($variables['request']['properties']['partners'])) {
            $variables['partner2'] = $commonGroundService->getResource($variables['request']['properties']['partners'][1]);
            $variables['partner1Submitted'] = true;
        }

        $variables['partner1'] = $commonGroundService->getResource($this->getUser()->getPerson());

        if ($request->isMethod('POST') && $request->get('partner1') && !$session->get('partners')) {
            $variables['partner1Submitted'] = true;
            $partner['contact']['emails'][]['email'] = $request->get('email');
            $partner['contact']['telephones'][]['telephone'] = $request->get('telephone');
            $partner['contact']['givenName'] = $request->get('givenName');
            $partner['contact']['familyName'] = $request->get('familyName');
            $partner['name'] = 'Instemmingsverzoek voor Huwelijk / Partnerschap';
            $partner['description'] = 'U heeft een instemmingsverzoek ontvangen als partners voor een Huwelijk/ Partnerschap.';
            $partner['request'] = $commonGroundService->cleanUrl(['component' => 'vrc', 'type' => 'requests', 'id' => $variables['request']['id']]);
            $partner['requester'] = $commonGroundService->cleanUrl(['component' => 'wrc', 'type' => 'organizations', 'id' => '68b64145-0740-46df-a65a-9d3259c2fec8']);
            $session->set('partners', $partner);
        } elseif ($request->isMethod('POST') && $request->get('partner2') && !$session->get('partners')) {
            $variables['partner2Submitted'] = true;
            $partner['contact']['emails'][]['email'] = $request->get('email');
            $partner['contact']['telephones'][]['telephone'] = $request->get('telephone');
            $partner['contact']['givenName'] = $request->get('givenName');
            $partner['contact']['familyName'] = $request->get('familyName');
            $partner['name'] = 'Instemmingsverzoek voor Huwelijk / Partnerschap';
            $partner['description'] = 'U heeft een instemmingsverzoek ontvangen als partners voor een Huwelijk/ Partnerschap.';
            $partner['request'] = $commonGroundService->cleanUrl(['component' => 'vrc', 'type' => 'requests', 'id' => $variables['request']['id']]);
            $partner['requester'] = $commonGroundService->cleanUrl(['component' => 'wrc', 'type' => 'organizations', 'id' => '68b64145-0740-46df-a65a-9d3259c2fec8']);
            $session->set('partners', $partner);
        } elseif ($request->isMethod('POST') && $session->get('partners')) {
            $currentRequest = $session->get('currentRequest');
            $currentRequest['properties']['partners'][0] = $session->get('partners');
            $currentRequest['properties']['partners'][1]['contact']['emails'][]['email'] = $request->get('email');
            $currentRequest['properties']['partners'][1]['contact']['telephones'][]['telephone'] = $request->get('telephone');
            $currentRequest['properties']['partners'][1]['contact']['givenName'] = $request->get('givenName');
            $currentRequest['properties']['partners'][1]['contact']['familyName'] = $request->get('familyName');
            $currentRequest['properties']['partners'][1]['name'] = 'Instemmingsverzoek voor Huwelijk / Partnerschap';
            $currentRequest['properties']['partners'][1]['description'] = 'U heeft een instemmingsverzoek ontvangen als partners voor een Huwelijk/ Partnerschap.';
            $currentRequest['properties']['partners'][1]['request'] = $commonGroundService->cleanUrl(['component' => 'vrc', 'type' => 'requests', 'id' => $variables['request']['id']]);
            $currentRequest['properties']['partners'][1]['requester'] = $commonGroundService->cleanUrl(['component' => 'wrc', 'type' => 'organizations', 'id' => '68b64145-0740-46df-a65a-9d3259c2fec8']);

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_ceremonie'));
        }

        return $variables;
    }

    /**
     * @Route("/ceremonie")
     * @Template
     */
    public function ceremonieAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        $variables['request'] = $session->get('currentRequest');
        $variables['products'] = $commonGroundService->getResourceList(['component' => 'pdc', 'type' => 'products'], ['groups.id' => '1cad775c-c2d0-48af-858f-a12029af24b3'])['hydra:member'];

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');

            $currentRequest['properties']['plechtigheid'] = $request->get('ceremonie');

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_ambtenaar'));
        }

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
     * @Route("/locatie")
     * @Template
     */
    public function locatieAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

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
