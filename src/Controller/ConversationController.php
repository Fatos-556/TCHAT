<?php

namespace App\Controller;


use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

/**
 * @Route ("/conversations", name="conversations.")
 */
class ConversationController extends AbstractController
{

    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository,
                                EntityManagerInterface $entityManager,
                                ConversationRepository $conversationRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }
    /**
     * @Route("/{id}", name="getConversation", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request): Response
    {
        $otherUser =$request->get('otherUser', 0);
        $otherUser = $this->userRepository->find($otherUser);

        if (is_null($otherUser)){
            throw new \Exception("the user was not found");
        }


        //todo: like the exception with yourself i need to create one exception for those who are not admins

        // cannot create a conversation with myself
        if ($otherUser->getId() === $this->getUser()->getId()){
            throw new \Exception("That's deep but you cannot create a conversation with yourself");
        }


        //todo: like the exception with yourself i need to create one exception for those who are not admins


//check if conversation already exists

        $conversation = $this->conversationRepository->findConversationByParticipants(
            $otherUser->getId(),
            $this->getUser()->getId()
        );

//dd($conversation);

        //create conversation if we dont have one

        if (count($conversation)) {
            throw new \Exception("The conversation already exists");
        }
            $conversation = new Conversation();

            //conversation pour moi
            $participant = new Participant();
            $participant ->setUser($this->getUser());
            $participant->setConversation($conversation);

            //conversation pour l'autre utilisateur
            $otherParticipant = new Participant();
            $otherParticipant ->setUser($otherUser);
            $otherParticipant->setConversation($conversation);

            $this->entityManager->getConnection()->beginTransaction();
            try {
                $this->entityManager->persist($conversation);
                $this->entityManager->persist($participant);
                $this->entityManager->persist($otherParticipant);
                $this->entityManager->flush();
                $this->entityManager->commit();

            }
            catch (\Exception $exception){
                $this->entityManager->rollback();
                throw $exception;
            }





      return $this->json([
          'id' => $conversation->getId()
      ], Response::HTTP_CREATED, [] , []);
    }


    /**
     * @Route ("/",name="", methods={"GET"})
     */

    public function getConvs(Request $request) {
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
//        dd($conversations);

$hubUrl = $this->getParameter('mercure.default_hub');

$this->addLink( $request, new Link('mercure', $hubUrl));


        return $this->json($conversations);
    }
}
