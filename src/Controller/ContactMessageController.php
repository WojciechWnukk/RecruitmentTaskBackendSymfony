<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ContactMessageController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/api/contact", name:'contact_form', methods:"POST")]
    public function submitContactForm(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $contact = new ContactMessage();
        $contact->setFirstName($data['firstName']);
        $contact->setLastName($data['lastName']);
        $contact->setEmail($data['email']);
        $contact->setMessage($data['message']);

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->json(['status' => 'success', 'data' => $data]);
    }

    #[Route("/api/contact", name:'contact_form', methods:"GET")]
    public function getContactMessages(): JsonResponse
    {
        $contactMessages = $this->entityManager->getRepository(ContactMessage::class)->findAll();

        return $this->json(['status' => 'success', 'data' => $contactMessages]);
    }
}
