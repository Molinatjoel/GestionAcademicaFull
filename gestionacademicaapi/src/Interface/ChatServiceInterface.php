<?php
namespace App\Interface;

use App\Entity\Chat;

interface ChatServiceInterface
{
    public function createChat(array $data): Chat;
    public function updateChat(Chat $chat, array $data): Chat;
    public function deleteChat(Chat $chat): void;
    public function getChatById(int $id): ?Chat;
    public function getAllChats(): array;
}
