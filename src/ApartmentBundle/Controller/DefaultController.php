<?php

namespace ApartmentBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;

use ApartmentBundle\Entity\Apartment;
use ApartmentBundle\Form\ApartmentType;

/**
 * Class DefaultController
 * @Route("/apartment")
 *
 * @package ApartmentBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * Number apartments per page
     */
    const LIMIT = 10;

    /**
     * @Route("/", name="list")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $page = abs(intval($request->query->get('page', 1)));
        $offset = ($page - 1) * self::LIMIT;

        $apartmentRepository = $this->getDoctrine()->getRepository(Apartment::class);
        $apartments = $apartmentRepository->getList(self::LIMIT, $offset);
        $apartmentsCount = $apartmentRepository->getTotal();

        return $this->render('ApartmentBundle::index.html.twig', [
            'apartments' => $apartments,
            'apartments_count' => $apartmentsCount,
            'current_page' => $page,
            'total_pages' => ceil($apartmentsCount / self::LIMIT),
        ]);
    }

    /**
     * @Route("/view/{id}", name="view", requirements={"id": "\d+"})
     * @Method({"GET"})
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction(Request $request, int $id)
    {
        $repository = $this->getDoctrine()->getRepository(Apartment::class);
        $apartment = $repository->find($id);

        if (!$apartment) {
            throw new NotFoundHttpException('Apartment with id #' . strval($id) . ' not found.');
        }

        return $this->render('ApartmentBundle::view.html.twig', ['apartment' => $apartment]);
    }

    /**
     * @Route("/create", name="create")
     * @Method({"GET", "POST"})
     *
     * @param Request       $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $apartment = new Apartment();

        $form = $this->createForm(ApartmentType::class, $apartment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $apartment = $form->getData();
            $apartment->setMoveInDate(new \DateTime($form->get('moveInDate')->getData()));
            $apartment->setPostedAt(new \Datetime());
            $apartment->setSecurityToken($apartment->generateToken());

            $em = $this->getDoctrine()->getManager();
            $em->persist($apartment);
            $em->flush();

            $this->sendMessage($apartment, 'created');

            $this->addFlash(
                'notice',
                'Your apartment was successfully submitted. Check your email for new message.'
            );

            return $this->redirectToRoute('view', ['id' => $apartment->getId()]);
        }

        return $this->render('ApartmentBundle::create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/edit/{id}", name="edit", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     *
     * @param Request $request
     * @param int     $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id)
    {
        $givenToken = strval($request->get('secret'));

        $apartment = $this->getApartment($id, $givenToken);

        $form = $this->createForm(ApartmentType::class, $apartment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $apartment = $form->getData();
            $apartment->setMoveInDate(new \DateTime($form->get('moveInDate')->getData()));
            $apartment->setEditedAt(new \Datetime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($apartment);
            $em->flush();

            $this->sendMessage($apartment, 'edited');

            $this->addFlash(
                'notice',
                'Your apartment was successfully changed.'
            );

            return $this->redirectToRoute('view', ['id' => $id]);
        }

        return $this->render('ApartmentBundle::edit.html.twig', [
            'form' => $form->createView(),
            'apartment' => $apartment
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", requirements={"id": "\d+"})
     * @Method({"GET"})
     *
     * @param Request $request
     * @param int     $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, int $id)
    {
        $givenToken = strval($request->get('secret'));

        $apartment = $this->getApartment($id, $givenToken);

        $em = $this->getDoctrine()->getManager();
        $em->remove($apartment);
        $em->flush();

        $this->sendMessage($apartment, 'deleted');

        $this->addFlash(
            'notice',
            'Your apartment was successfully deleted.'
        );

        return $this->redirectToRoute('list');

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
                ) ,
                'text/html'
            );

        $this->get('mailer')->send($message);

        return true;
    }

    /**
     * Load apartment entity with security check
     *
     * @param int    $id
     * @param string $givenToken
     * @return Apartment|null|object
     */
    protected function getApartment(int $id, string $givenToken)
    {
        if (empty($givenToken)) {
            throw new AccessDeniedHttpException('You should use special link from email message for access to this page.');
        }

        $repository = $this->getDoctrine()->getRepository(Apartment::class);
        $apartment = $repository->find($id);

        if (!$apartment) {
            throw new NotFoundHttpException('Apartment with id #' . strval($id) . ' not found.');
        }

        if ($givenToken !== $apartment->getSecurityToken()) {
            throw new AccessDeniedHttpException('You should use special link from email message for access to this page.');
        }

        return $apartment;
    }
}
