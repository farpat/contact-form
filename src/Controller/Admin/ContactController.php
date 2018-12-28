<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @Route("/admin/contact/") */
class ContactController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct (TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("", name="admin.contact.index")
     * @param ContactRepository $contactRepository
     *
     * @return Response
     */
    public function index (ContactRepository $contactRepository)
    {
        $contacts = $contactRepository->findAll();
        return $this->render('admin/contact/index.html.twig', compact('contacts'));
    }

    /**
     * @Route("treat/{contact}", name="admin.contact.treat", methods="PATCH", requirements={"contact"="\d+"}))
     * @param Contact $contact
     * @param Request $request
     * @param ObjectManager $manager
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function treat (Contact $contact, Request $request, ObjectManager $manager)
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('admin.contact.treat.' . $contact->getId(), $token)) {
            $contact->setTreatedAt(new \DateTime());
            $manager->flush();
        }

        return $this->redirectToRoute('admin.contact.index');
    }
}
