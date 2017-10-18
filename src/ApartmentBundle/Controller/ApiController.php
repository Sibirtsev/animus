<?php

namespace ApartmentBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\View\View as ResponseView;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use ApartmentBundle\Entity\Apartment;

use ApartmentBundle\Exception\PermissionException;

class ApiController extends FOSRestController
{
    /**
     * Get a collections of Apartments.
     *
     * @Rest\Get("/api/apartment", name="api_list")
     * @ApiDoc(
     *     resource=true,
     *     description="Get all apartment records",
     * )
     * @View(serializerGroups={"apartment"})
     */
    public function listAction()
    {
        $apartmentRepository = $this->getDoctrine()->getRepository(Apartment::class);
        $apartments = $apartmentRepository->findActiveApartments();
        if ($apartments === null) {
            return new ResponseView("there are no users exist", Response::HTTP_NOT_FOUND);
        }
        return $apartments;
    }

    /**
     * Get single Apartment by id
     *
     * @Rest\Get("/api/apartment/{id}", name="api_view", requirements={"id": "\d+"})
     * @ApiDoc()
     * @View(serializerGroups={"apartment"})
     * @param int $id
     * @return Apartment|ResponseView|null|object
     */
    public function viewAction(int $id)
    {
        $repository = $this->getDoctrine()->getRepository(Apartment::class);
        $apartment = $repository->find($id);

        if (!$apartment) {
            return new ResponseView("there are no users exist", Response::HTTP_NOT_FOUND);
        }

        return $apartment;
    }

    /**
     * Create new apartment
     *
     * @Rest\Post("/api/apartment", name="api_create")
     * @ApiDoc(
     *     description="Create a new Apartment",
     *     input="ApartmentBundle\Form\ApartmentType"
     * )
     * @View(serializerGroups={"apartment"})
     * @param Request $request
     * @return ResponseView
     */
    public function createAction(Request $request)
    {
        $apartment = new Apartment();

        list($apartment, $errors) = $this->fillAndValidateApartment($apartment, $request);

        if (count($errors) > 0) {
            return new ResponseView(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($apartment);
        $em->flush();

        $this->sendMessage($apartment, 'created');

        return new ResponseView($apartment, Response::HTTP_CREATED);
    }

    /**
     * Fill Apartment from request and validate
     *
     * @param Apartment $apartment
     * @param Request   $request
     * @return array
     */
    protected function fillAndValidateApartment(Apartment $apartment, Request $request): array
    {
        $apartment->setStreet($request->get('street'));
        $apartment->setTown($request->get('town'));
        $apartment->setCountry($request->get('country'));
        $apartment->setPostCode($request->get('post_code'));

        try {
            $moveInDate = $request->get('move_in_date');
            if (empty($moveInDate)) {
                return [
                    $apartment,
                    [[
                        'property_path' => 'move_in_date',
                        'message' => 'This value should not be blank.'
                    ]]
                ];
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $moveInDate)) {
                return [
                    $apartment,
                    [[
                        'property_path' => 'move_in_date',
                        'message' => 'This value should be in ISO Date Format: YYYY-MM-DD.'
                    ]]
                ];
            }
            $moveInDate = new \DateTime($moveInDate);
        } catch (\Exception $e) {
            return [
                $apartment,
                [[
                    'property_path' => 'move_in_date',
                    'message' => 'This value should be in ISO Date Format: YYYY-MM-DD.'
                ]]
            ];
        }

        $apartment->setMoveInDate($moveInDate);
        $apartment->setEmail($request->get('email'));

        $apartment->setPostedAt(new \Datetime());
        $apartment->setSecurityToken($apartment->generateToken());

        $validator = $this->get('validator');
        $errors = $validator->validate($apartment);

        return [
            $apartment,
            $errors
        ];
    }

    /**
     * Send email message
     *
     * @param Apartment $apartment
     * @param string    $messageType
     * @return bool
     */
    protected function sendMessage(Apartment $apartment, $messageType = 'created')
    {
        $templateMapping = [
            'created' => 'ApartmentBundle:emails:created.html.twig',
            'edited' => 'ApartmentBundle:emails:edited.html.twig',
            'deleted' => 'ApartmentBundle:emails:deleted.html.twig',
        ];

        $topicsMapping = [
            'created' => 'Your apartment was successfully submitted',
            'edited' => 'Your apartment was successfully changed',
            'deleted' => 'Your apartment was successfully deleted',
        ];

        if (!array_key_exists($messageType, $templateMapping)) {
            return false;
        }

        $message = (new \Swift_Message())
            ->setFrom('noreply@example.com')
            ->setTo($apartment->getEmail())
            ->setSubject($topicsMapping[$messageType])
            ->setBody(
                $this->renderView(
                    $templateMapping[$messageType],
                    ['apartment' => $apartment]
                ),
                'text/html'
            );

        $this->get('mailer')->send($message);

        return true;
    }

    /**
     * Modify exists Apartment by id
     *
     * @Rest\Put("/api/apartment/{id}", name="api_edit", requirements={"id": "\d+"})
     * @ApiDoc(
     *     headers={
     *         {
     *             "name"="X-AUTHORIZE-KEY",
     *             "description"="Authorization key",
     *             "required"=true,
     *         }
     *     },
     *     description="Modify exists Apartment",
     *     input="ApartmentBundle\Form\ApartmentType"
     * )
     * @View(serializerGroups={"apartment"})
     * @param Request $request
     * @param int     $id
     * @return ResponseView
     */
    public function editAction(Request $request, int $id)
    {
        $givenToken = strval($request->headers->get('X-AUTHORIZE-KEY'));

        try {
            $apartment = $this->getApartment($id, $givenToken);
        } catch (PermissionException $e) {
            return new ResponseView(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        list($apartment, $errors) = $this->fillAndValidateApartment($apartment, $request);

        if (count($errors) > 0) {
            return new ResponseView(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $apartment->setEditedAt(new \Datetime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($apartment);
        $em->flush();

        $this->sendMessage($apartment, 'edited');

        return new ResponseView($apartment, Response::HTTP_OK);
    }

    /**
     * Load apartment entity with security check
     *
     * @param int    $id
     * @param string $givenToken
     * @return Apartment
     * @throws PermissionException
     */
    protected function getApartment(int $id, string $givenToken): Apartment
    {
        if (empty($givenToken)) {
            throw new PermissionException('You should use special link from email message for access to this page.');
        }

        $repository = $this->getDoctrine()->getRepository(Apartment::class);
        $apartment = $repository->find($id);

        if (!$apartment) {
            throw new PermissionException('Apartment with id #' . strval($id) . ' not found.');
        }

        if ($givenToken !== $apartment->getSecurityToken()) {
            throw new PermissionException('You should use special link from email message for access to this page.');
        }

        return $apartment;
    }

    /**
     * Deletes single apartment by id
     *
     * @Rest\Delete("/api/apartment/{id}", name="api_delete", requirements={"id": "\d+"})
     * @ApiDoc(
     *     headers={
     *         {
     *             "name"="X-AUTHORIZE-KEY",
     *             "description"="Authorization key",
     *             "required"=true,
     *         }
     *     }
     * )
     * @param Request $request
     * @param int     $id
     * @return ResponseView
     */
    public function deleteAction(Request $request, int $id)
    {
        $givenToken = strval($request->headers->get('X-AUTHORIZE-KEY'));

        try {
            $apartment = $this->getApartment($id, $givenToken);
        } catch (PermissionException $e) {
            return new ResponseView(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($apartment);
        $em->flush();

        $this->sendMessage($apartment, 'deleted');

        return new ResponseView(null, Response::HTTP_NO_CONTENT);
    }
}
