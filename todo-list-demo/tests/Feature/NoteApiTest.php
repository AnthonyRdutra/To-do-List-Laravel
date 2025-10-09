<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Note;

class NoteApiTest extends TestCase
{
    public function testLoginandCreateNote()
    {
        // Cria usuário MongoDB real
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => bcrypt('123456'),
        ]);

        // Autentica com Passport (ou Sanctum, se for o caso)
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'tester@example.com',
            'password' => '123456',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');

        // Cria uma nota autenticada
        $noteResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/notes', [
            'title' => 'Nota MongoDB',
            'content' => 'Criada no Atlas com autenticação JWT',
        ]);

        $noteResponse->assertStatus(201)
                     ->assertJsonStructure([
                         'success',
                         'note' => ['id', 'title', 'content']
                     ]);
    }
}
