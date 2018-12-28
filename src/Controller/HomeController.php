<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController
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
     * @Route("/", name="home.index")
     */
    public function index ()
    {
        $contactForm = $this->createForm(ContactType::class, null, ['action' => $this->generateUrl('home.contact')]);

        return $this->render('home/index.html.twig', ['form' => $contactForm->createView()]);
    }

    /**
     * @Route("/contact", name="home.contact", methods="POST")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function postContact (Request $request, ObjectManager $manager)
    {
        $contact = new Contact;
        $contactForm = $this->createForm(ContactType::class, $contact);

        $contactForm->handleRequest($request);

        if ($contactForm->isValid()) {
            $contact->setIpAdress($request->getClientIp());
            $manager->persist($contact);
            $manager->flush();
            $this->addFlash('success', $this->translator->trans('Successful contact! Thank you :)'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Oh oh'));
        }

        return $this->redirectToRoute('home.index');
    }
}
