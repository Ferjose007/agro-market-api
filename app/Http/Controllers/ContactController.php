<?php
namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Función PÚBLICA para que los clientes envíen mensajes
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        ContactMessage::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Mensaje enviado correctamente. Te contactaremos pronto.'
        ]);
    }

    // Función de ADMIN para ver todos los mensajes
    public function index()
    {
        // Traemos los mensajes ordenados por fecha de creación (los más nuevos primero)
        $messages = ContactMessage::orderBy('created_at', 'desc')->get();
        return response()->json($messages);
    }

    // Función de ADMIN para marcar un mensaje como leído
    public function markAsRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        return response()->json(['status' => 'success']);
    }
}