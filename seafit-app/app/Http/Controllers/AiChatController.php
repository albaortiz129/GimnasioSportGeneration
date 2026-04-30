<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Controlador del chat con IA.
 * Usa OpenRouter para responder.
 * Si OpenRouter falla o no responde, muestra el correo de soporte.
 */
class AiChatController extends Controller
{
    /**
     * Recibe la pregunta del chat y devuelve una respuesta en JSON.
     */
    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|min:2|max:500',
        ]);

        $mensaje = trim((string) $data['message']); // Valida el mensaje

        $emailSoporte = (string) config('services.ai_chat.support_email', 'soporte.seafit@gmail.com'); // Obtiene el email de soporte
        $rules = config('entrenar_IA.rules', []); // Obtiene las reglas de entrenamiento
        $rules = is_array($rules) ? $rules : []; // Si las reglas no son un array, las convierte a un array vacío

        $apiKey = trim((string) config('services.openrouter.api_key', '')); // Obtiene la API key
        if ($apiKey === '') { // Si la API key está vacía
            return response()->json([
                'reply' => $this->supportFallbackText($emailSoporte), // Devuelve el mensaje de soporte
                'source' => 'fallback', // Indica que la respuesta es un fallback
            ]);
        }

        $respuestaOpenRouter = $this->answerFromOpenRouter($mensaje, $rules, $emailSoporte, $apiKey); // Llama a OpenRouter
        if ($respuestaOpenRouter !== null) { // Si OpenRouter responde
            return response()->json([
                'reply' => $respuestaOpenRouter, // Devuelve la respuesta
                'source' => 'openrouter', // Indica que la respuesta es de OpenRouter
            ]);
        }

        return response()->json([
            'reply' => $this->supportFallbackText($emailSoporte), // Devuelve el mensaje de soporte
            'source' => 'fallback', // Indica que la respuesta es un fallback
        ]);
    }

    /**
     * Llama a OpenRouter y devuelve la respuesta del modelo.
     * @param  array<int, array<string, mixed>>  $rules
     */
    private function answerFromOpenRouter(string $mensaje, array $rules, string $emailSoporte, string $apiKey): ?string
    {
        $baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/'); // Obtiene la URL base
        $model = (string) config('services.openrouter.model', 'openrouter/free'); // Obtiene el modelo
        $systemPrompt = $this->buildSystemPrompt($rules, $emailSoporte); // Construye el prompt del sistema

        try { // Intenta hacer la petición a OpenRouter
            $response = Http::timeout(20) // Timeout de 20 segundos
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}", // API key para autenticación
                    'Content-Type' => 'application/json', // Tipo de contenido
                    'HTTP-Referer' => (string) config('app.url'), // Referer para seguridad
                    'X-Title' => 'SeaFit', // Título de la aplicación
                    'Accept' => 'application/json', // Tipo de respuesta aceptada
                ])
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model, // Modelo a usar
                    'messages' => [ // Mensajes del chat
                        ['role' => 'system', 'content' => $systemPrompt], // Prompt del sistema
                        ['role' => 'user', 'content' => $mensaje], // Mensaje del usuario
                    ],
                    'temperature' => 0.0, // Temperatura baja para respuestas más precisas
                    'top_p' => 0.2, // Top-p bajo para respuestas más precisas
                    'max_tokens' => 220, // Máximo de tokens
                ]);

            if (!$response->ok()) { // Si la petición no fue exitosa
                Log::warning('OpenRouter devolvió error en chat IA', [
                    'status' => $response->status(), // Código de estado HTTP
                    'body' => $response->body(), // Cuerpo de la respuesta
                ]);

                return null;
            }

            $reply = trim((string) data_get($response->json(), 'choices.0.message.content', '')); // Obtiene la respuesta
            return $reply !== '' ? $reply : null; // Si la respuesta no está vacía, la devuelve
        } catch (\Throwable $e) { // Si hay un error
            Log::warning('Fallo en OpenRouter dentro del chat IA', [
                'error' => $e->getMessage(), // Mensaje de error
            ]);

            return null;
        }
    }

    /**
     * Construye el prompt del sistema con reglas tipo FAQ.
     * @param  array<int, array<string, mixed>>  $rules
     */
    private function buildSystemPrompt(array $rules, string $emailSoporte): string
    {
        $lineasFaq = []; // Array para almacenar las líneas FAQ 

        foreach ($rules as $rule) {
            $tags = $rule['tags'] ?? []; // Obtiene las etiquetas
            $answer = trim((string) ($rule['answer'] ?? '')); // Obtiene la respuesta

            if (!is_array($tags) || $tags === [] || $answer === '') { // Si las etiquetas no son un array o están vacías o la respuesta está vacía
                continue;
            }

            $tagsLimpios = array_values(array_filter(array_map('strval', $tags), function (string $tag): bool { // Filtra las etiquetas
                return trim($tag) !== '';
            }));

            if ($tagsLimpios === []) { // Si las etiquetas están vacías
                continue;
            }

            // Cada regla se expresa como disparadores + respuesta oficial.
            $lineasFaq[] = 'Preguntas parecidas a: ' . implode(' | ', $tagsLimpios) . "\n"
                . 'Respuesta oficial: ' . $answer;

            if (count($lineasFaq) >= 30) { // Si hay 30 líneas FAQ, sale del bucle
                break;
            }
        }

        $bloqueFaq = implode("\n\n", $lineasFaq); // Une las líneas FAQ

        return "Eres el asistente oficial de SeaFit.\n"
            . "Responde siempre en español, de forma breve y clara.\n"
            . "Usa solo la información del bloque FAQ.\n"
            . "No inventes datos ni políticas.\n"
            . "Si la pregunta no está cubierta por el FAQ, responde exactamente:\n"
            . "No tengo esa información ahora. Puedes contactar con nosotros en {$emailSoporte}.\n\n"
            . "FAQ de SeaFit:\n{$bloqueFaq}";
    }

    /**
     * Mensaje final cuando no hay respuesta disponible.
     */
    private function supportFallbackText(string $emailSoporte): string
    {
        return "No tengo esa información ahora. Puedes contactar con nosotros en {$emailSoporte}.";
    }
}

