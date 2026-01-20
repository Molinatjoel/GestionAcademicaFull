<?php
namespace App\Controller;

use App\Repository\ChatUserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MercureController extends AbstractController
{
    private Security $security;
    private ChatUserRepository $chatUserRepository;
    private string $mercureJwtSecret;
    private string $mercurePublicUrl;

    public function __construct(
        Security $security,
        ChatUserRepository $chatUserRepository,
        #[Autowire(env: 'MERCURE_JWT_SECRET')] string $mercureJwtSecret,
        #[Autowire(env: 'MERCURE_PUBLIC_URL')] string $mercurePublicUrl
    ) {
        $this->security = $security;
        $this->chatUserRepository = $chatUserRepository;
        $this->mercureJwtSecret = $mercureJwtSecret;
        $this->mercurePublicUrl = $mercurePublicUrl;
    }

    #[Route('/api/mercure-token', methods: ['GET'])]
    public function token(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->json(['error' => 'No autenticado'], Response::HTTP_UNAUTHORIZED);
        }

        $memberships = $this->chatUserRepository->findBy(['user' => $user]);
        $topics = [];
        foreach ($memberships as $membership) {
            $chat = $membership->getChat();
            if ($chat && $chat->getIdChat()) {
                $topics[] = '/chats/' . $chat->getIdChat();
            }
        }
        $topics = array_values(array_unique($topics));

        $payload = [
            'mercure' => [
                'subscribe' => $topics,
            ],
            'sub' => (string) $user->getId(),
            'exp' => time() + 3600,
        ];

        $token = JWT::encode($payload, $this->mercureJwtSecret, 'HS256');

        return $this->json([
            'token' => $token,
            'topics' => $topics,
            'mercure_url' => $this->mercurePublicUrl,
        ]);
    }
}
