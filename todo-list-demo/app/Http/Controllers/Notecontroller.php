<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class Notecontroller extends Controller
{
    public function index()
    {
        print(__METHOD__ . "\n");

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Usuário não autenticado'], 401);
        }

        // Busca apenas notas do usuário autenticado
        $notes = Note::where('user_id', (string) $user->_id ?? $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'notes' => $notes->map(function ($note) {
                return [
                    'id' => (string) ($note->_id ?? $note->id),
                    'title' => $note->title,
                    'content' => $note->content,
                    'created_at' => $note->created_at,
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'Usuário não autenticado'], 401);
            }


            $validated = $request->validate([
                'title'   => 'required|string|max:100',
                'content' => 'required|string',
                'color'   => 'nullable|string|max:20'
            ]);


            $note = \App\Models\Note::create([
                'user_id' => (string) ($user->_id ?? $user->id),
                'title'   => $validated['title'],
                'content' => $validated['content'],
                'color'   => $validated['color'] ?? '#ffffff',
            ]);


            return response()->json([
                'success' => true,
                'note' => [
                    'id'         => (string) $note->id,
                    'title'      => $note->title,
                    'content'    => $note->content,
                    'color'      => $note->color,
                    'created_at' => $note->created_at
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id, Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $note = \App\Models\Note::where('_id', $id)
                ->where('user_id', (string) $user->_id)
                ->first();

            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nota não encontrada ou não pertence ao usuário.'
                ], 404);
            }

            $note->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nota excluída com sucesso.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            // Validação dos campos enviados
            $validated = $request->validate([
                'title'   => 'required|string|max:255',
                'content' => 'required|string'
            ]);

            // Busca a nota do usuário autenticado
            $note = \App\Models\Note::where('_id', $id)
                ->where('user_id', (string) $user->_id)
                ->first();

            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nota não encontrada ou não pertence ao usuário.'
                ], 404);
            }

            // Atualiza e salva
            $note->title = $validated['title'];
            $note->content = $validated['content'];
            $note->save();

            return response()->json([
                'success' => true,
                'message' => 'Nota atualizada com sucesso.',
                'note' => $note
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
