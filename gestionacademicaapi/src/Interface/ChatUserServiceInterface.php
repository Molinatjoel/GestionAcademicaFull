<?php
namespace App\Interface;

use App\Entity\ChatUser;

interface ChatUserServiceInterface
{
    public function createChatUser(array $data): ChatUser;
    public function updateChatUser(ChatUser $chatUser, array $data): ChatUser;
    public function deleteChatUser(ChatUser $chatUser): void;
    public function getChatUserById(int $id): ?ChatUser;
    public function getAllChatUsers(): array;
}
