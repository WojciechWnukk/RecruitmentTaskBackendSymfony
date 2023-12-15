<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactMessageController extends AbstractController
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route("/api/contact", name: 'contact_form_post', methods: "POST")]
    public function submitContactForm(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $contact = new ContactMessage();
        $contact->setFirstName($data['firstName']);
        $contact->setLastName($data['lastName']);
        $contact->setEmail($data['email']);
        $contact->setMessage($data['message']);

        $errors = $this->validator->validate($contact);
        $errors->addAll($this->validator->validate($contact->getEmail(), [
            new \Symfony\Component\Validator\Constraints\Email(),
        ]));

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(['status' => 'error', 'errors' => $errorMessages], 400);
        }

        // Zapis do bazy
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->json(['status' => 'success', 'data' => $data]);
    }

    #[Route("/api/contact", name: 'contact_form', methods: "GET")]
    public function getContactMessages(): JsonResponse
    {
        $contactMessages = $this->entityManager->getRepository(ContactMessage::class)->findAll();

        $formattedContactMessages = [];
        foreach ($contactMessages as $message) {
            $formattedContactMessages[] = [
                'id' => $message->getId(),
                'firstName' => $message->getFirstName(),
                'lastName' => $message->getLastName(),
                'email' => $message->getEmail(),
                'message' => $message->getMessage(),
            ];
        }

        return $this->json(['status' => 'success', 'data' => $formattedContactMessages]);
    }
}
