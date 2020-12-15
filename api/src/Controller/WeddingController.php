<?php

// src/Controller/DefaultController.php

namespace App\Controller;

use App\Service\MailingService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use http\Client;
use Ramsey\Uuid\Uuid;
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
     * @Route("/huwelijk/{id}")
     * @Template
     */
    public function huwelijkAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home', $id = null)
    {
        $variables = [];

        if ($id !== null) {
            $currentRequest = $commonGroundService->getResource(['component' => 'vrc', 'type' => 'requests', 'id' => $id]);
            $session->set('currentRequest', $currentRequest);
        }

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

            $currentRequest = $commonGroundService->createResource($variables['request'], ['component' => 'vrc', 'type' => 'requests']);

            $session->set('currentRequest', $currentRequest);
        }

        if ($request->isMethod('POST')) {

            $currentRequest = $session->get('currentRequest');
            $currentRequest['properties']['type'] = $request->get('type');

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];

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

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }
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

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }
            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_ambtenaren'));
        }

        return $variables;
    }

    /**
     * @Route("/ambtenaren")
     * @Template
     */
    public function ambtenarenAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        $variables['request'] = $session->get('currentRequest');
        $variables['products'] = $commonGroundService->getResourceList(['component' => 'pdc', 'type' => 'products'], ['groups.id' => '7f4ff7ae-ed1b-45c9-9a73-3ed06a36b9cc'])['hydra:member'];

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');

            $currentRequest['properties']['ambtenaar'] = $request->get('ambtenaar');

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_locatie'));
        }

        return $variables;
    }

    /**
     * @Route("/locatie")
     * @Template
     */
    public function locatieAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        $variables['request'] = $session->get('currentRequest');
        $variables['products'] = $commonGroundService->getResourceList(['component' => 'pdc', 'type' => 'products'], ['groups.id' => '170788e7-b238-4c28-8efc-97bdada02c2e'])['hydra:member'];

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');

            $currentRequest['properties']['locatie'] = $request->get('locatie');

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_datum'));
        }

        return $variables;
    }



    /**
     * @Route("/datum")
     * @Template
     */
    public function datumAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];
        $variables['request'] = $session->get('currentRequest');

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');

            $date = $request->get('datum');

            $currentRequest['properties']['datum'] = str_replace('(Central European Standard Time)', '', $date);

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_getuigen'));
        }

        return $variables;
    }

    /**
     * @Route("/getuigen")
     * @Template
     */
    public function getuigenAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');

            if (isset($currentRequest['properties']['getuigen']) && count($currentRequest['properties']['getuigen']) == 4) {
                $this->addFlash('warning', 'u heeft al 4 getuigen opgegeven');
            } else {
                if (isset($currentRequest['properties']['getuigen'])) {
                    $index = count($currentRequest['properties']['getuigen']) + 1;
                } else {
                    $index = 1;
                }
                $currentRequest['properties']['getuigen'][$index]['contact']['emails'][]['email'] = $request->get('email');
                $currentRequest['properties']['getuigen'][$index]['contact']['telephones'][]['telephone'] = $request->get('telephone');
                $currentRequest['properties']['getuigen'][$index]['contact']['givenName'] = $request->get('givenName');
                $currentRequest['properties']['getuigen'][$index]['contact']['familyName'] = $request->get('familyName');
                $currentRequest['properties']['getuigen'][$index]['name'] = 'Instemmingsverzoek voor Huwelijk / Partnerschap';
                $currentRequest['properties']['getuigen'][$index]['description'] = 'U heeft een instemmingsverzoek ontvangen als getuigen voor een Huwelijk/ Partnerschap.';
                $currentRequest['properties']['getuigen'][$index]['request'] = $commonGroundService->cleanUrl(['component' => 'vrc', 'type' => 'requests', 'id' => $currentRequest['id']]);
                $currentRequest['properties']['getuigen'][$index]['requester'] = $commonGroundService->cleanUrl(['component' => 'wrc', 'type' => 'organizations', 'id' => '68b64145-0740-46df-a65a-9d3259c2fec8']);

                if (!empty($currentRequest['children'])) {
                    $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
                }

                $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];

                $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
                $session->set('currentRequest', $currentRequest);
            }
        }

        $variables['request'] = $session->get('currentRequest');

        if (isset($variables['request']['properties']['getuigen'])) {
            $variables['getuigen'] = $variables['request']['properties']['getuigen'];
        } else {
            $variables['getuigen'] = [];
        }

        return $variables;
    }

    /**
     * @Route("/extra")
     * @Template
     */
    public function extraAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];
        $variables['products'] = $commonGroundService->getResourceList(['component' => 'pdc', 'type' => 'products'], ['groups.id' => 'f8298a12-91eb-46d0-b8a9-e7095f81be6f'])['hydra:member'];

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');
            $currentRequest['properties']['extras'][] = $request->get('extra');

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];

            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);
        }

        $variables['request'] = $session->get('currentRequest');

        return $variables;
    }

    /**
     * @Route("/betalen")
     * @Template
     */
    public function betalenAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        if ($session->get('mollieId')) {
            $currentRequest = $session->get('currentRequest');
            $requestUrl = $commonGroundService->cleanUrl(['component' => 'vrc', 'type' => 'organizations', 'id' => $currentRequest['id']]);
            $id = $session->get('mollieId');
            $session->remove('mollieId');
            $headers = [
                'Authorization' => 'Bearer test_WaWzFksV6TDFC9k228jguK9xgzVQkt',
                'Accept'        => 'application/json',
            ];

            $client = new \GuzzleHttp\Client([
                // Base URI is used with relative requests
                'base_uri' => 'https://api.mollie.com',
                // You can set any number of default request options.
                'timeout'  => 2.0,
            ]);

            $response = $client->request('GET', '/v2/payments/'.$id, [
                'headers' => $headers,
            ]);

            $response = json_decode($response->getBody()->getContents(), true);

            $item = [];
            $item['name'] = 'betaling';
            $item['description'] = 'betaling';
            $item['quantity'] = 1;
            $item['price'] = strval($response['amount']['value']);
            $item['priceCurrency'] = 'EUR';

            $invoice = [];
            $invoice['customer'] = $requestUrl;
            $invoice['name'] = 'betaling';
            $invoice['items'][] = $item;
            $invoice['targetOrganization'] = $requestUrl;
            $invoice['price'] = strval($response['amount']['value']);
            $invoice['priceCurrency'] = 'EUR';
            $invoice['paid'] = true;
            $invoice['reference'] = Uuid::uuid4();

            $invoice = $commonGroundService->createResource($invoice, ['component' => 'bc', 'type' => 'invoices']);
            $invoiceUrl = $commonGroundService->cleanUrl(['component' => 'bc', 'type' => 'invoices', 'id' => $invoice['id']]);

            $currentRequest['properties']['invoice'] = $invoiceUrl;
            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];

            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_reservering'));

        }

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');
            $order = $commonGroundService->getResource($currentRequest['order']);

            if (key_exists('invoice', $currentRequest['properties']) && $currentRequest['properties']['invoice'] != null) {
                $invoice = $commonGroundService->getResource($currentRequest['properties']['invoice']);
                if ($invoice['dateCreated'] < $order['dateModified']) {
                    $commonGroundService->deleteResource($invoice);
                    unset($invoice);
                }
            }
            if (!isset($invoice)) {
                $body = [
                    'amount'      => [
                        'currency' => 'EUR',
                        'value'    => $order['price'],
                    ],
                    'description' => 'huwelijk',
                    'redirectUrl' => $request->getUri(),
                    'locale'      => 'en_US',
                ];

                $headers = [
                    'Authorization' => 'Bearer test_WaWzFksV6TDFC9k228jguK9xgzVQkt',
                    'Accept'        => 'application/json',
                ];

                $client = new \GuzzleHttp\Client([
                    // Base URI is used with relative requests
                    'base_uri' => 'https://api.mollie.com',
                    // You can set any number of default request options.
                    'timeout'  => 2.0,
                ]);

                $response = $client->request('POST', '/v2/payments', [
                    'form_params'  => $body,
                    'content_type' => 'application/x-www-form-urlencoded',
                    'headers'      => $headers,
                ]);

                $response = json_decode($response->getBody()->getContents(), true);

                $session->set('mollieId', $response['id']);

                return $this->redirect($response['_links']['checkout']['href']);
            }

        }

        $variables['request'] = $session->get('currentRequest');

        return $variables;
    }

    /**
     * @Route("/melding")
     * @Template
     */
    public function meldingAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];
        $variables['request'] = $session->get('currentRequest');

        if ($request->isMethod('POST')) {
            $currentRequest = $session->get('currentRequest');

            $melding['status'] = 'submitted';
            $melding['submitters'][0]['brp'] = $this->getUser()->getPerson();
            $melding['properties'] = [];
            $melding['organization'] = $commonGroundService->cleanUrl(['component' => 'wrc', 'type' => 'organizations', 'id' => '68b64145-0740-46df-a65a-9d3259c2fec8']);
            $melding['requestType'] = $commonGroundService->cleanUrl(['component' => 'vtc', 'type' => 'request_types', 'id' => '146cb7c8-46b9-4911-8ad9-3238bab4313e']);
            $melding['parent'] = '/requests/'.$currentRequest['id'];

            if (isset($currentRequest['properties']['partners'])) {
                $melding['properties']['partner-melding'] = $currentRequest['properties']['partners'];
            }

            $melding['properties']['datum-melding'] = new \DateTime('now');

            if (isset($currentRequest['properties']['getuigen'])) {
                $melding['properties']['getuige-melding'] = $currentRequest['properties']['getuigen'];
            }
            $melding = $commonGroundService->saveResource($melding, ['component' => 'vrc', 'type' => 'requests']);

            $currentRequest = $commonGroundService->getResource(['component' => 'vrc', 'type' => 'requests', 'id' => $currentRequest['id']]);

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];
            $currentRequest['properties']['melding'] = $commonGroundService->cleanUrl(['component' => 'vrc', 'type' => 'requests', 'id' => $melding['id']]);
            $currentRequest['children'][0] = '/requests/'.$melding['id'];
            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $session->set('currentRequest', $currentRequest);

            return $this->redirect($this->generateUrl('app_wedding_reservering'));
        }

        return $variables;
    }

    /**
     * @Route("/reservering")
     * @Template
     */
    public function reserveringAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];
        $variables['request'] = $session->get('currentRequest');

        if ($request->isMethod('POST') && $request->get('submit')) {
            $currentRequest = $session->get('currentRequest');
            $currentRequest['status'] = 'submitted';

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];

            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $this->addFlash('success', 'Uw verzoek is ingediend');
            return $this->redirect($this->generateUrl('app_wedding_requests'));

        } elseif ($request->isMethod('POST') && $request->get('cancel')) {
            $currentRequest = $session->get('currentRequest');
            $currentRequest['status'] = 'cancelled';

            if (!empty($currentRequest['children'])) {
                $currentRequest['children'][0] = '/requests/'.$currentRequest['children'][0]['id'];
            }

            $currentRequest['submitters'][0] = '/submitters/'.$currentRequest['submitters'][0]['id'];

            $currentRequest = $commonGroundService->saveResource($currentRequest, ['component' => 'vrc', 'type' => 'requests']);
            $this->addFlash('warning', 'Uw verzoek is gecancelled');
            return $this->redirect($this->generateUrl('app_wedding_requests'));
        }

        return $variables;
    }

    /**
     * @Route("/requests")
     * @Template
     */
    public function requestsAction(Session $session, Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, string $slug = 'home')
    {
        $variables = [];

        $variables['requests'] = $commonGroundService->getResourceList(['component' => 'vrc', 'type' => 'requests'], ['submitters.brp' => $this->getUser()->getPerson()])['hydra:member'];

        return $variables;
    }
}
