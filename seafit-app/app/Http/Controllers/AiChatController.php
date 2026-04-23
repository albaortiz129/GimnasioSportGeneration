<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Controlador del chat IA.
 *
 * Flujo de respuesta (de mas controlado a mas abierto):
 * 1) Motor Python local (scikit-learn).
 * 2) Reglas locales en PHP.
 * 3) OpenRouter.
 * 4) Mensaje final de soporte.
 */
class AiChatController extends Controller
{
    /**
     * Endpoint del chat.
     * Recibe el mensaje y devuelve un JSON con la respuesta.
     */
    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|min:2|max:500',
        ]);

        $mensajeOriginal = trim((string) $data['message']);
        $mensaje = $this->normalizarTexto($mensajeOriginal);
        $emailSoporte = (string) config('services.ai_chat.support_email', 'soporte.seafit@gmail.com');

        $rules = config('entrenar_IA.rules', []);
        $rules = is_array($rules) ? $rules : [];

        // 1) Intento con Python (ML local).
        $respuestaPython = $this->answerFromPythonModel($mensajeOriginal, $rules);
        if ($respuestaPython !== null) {
            return response()->json([
                'reply' => $respuestaPython['answer'],
                'source' => 'python_ml',
                'intent' => $respuestaPython['intent'],
                'confidence' => $respuestaPython['confidence'],
            ]);
        }

        // 2) Si Python no responde, se prueban reglas locales.
        $respuestaLocal = $this->answerFromTrainingRules($mensaje, $rules);
        if ($respuestaLocal !== null) {
            return response()->json([
                'reply' => $respuestaLocal['answer'],
                'source' => 'local',
                'intent' => $respuestaLocal['intent'],
            ]);
        }

        // 3) Si no hay API key de OpenRouter, se da fallback directo.
        $apiKey = trim((string) config('services.openrouter.api_key'));
        if ($apiKey === '') {
            return response()->json([
                'reply' => $this->supportFallbackText($emailSoporte),
                'source' => 'fallback',
            ]);
        }

        // 4) OpenRouter como ultima capa de respuesta.
        try {
            $baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
            $model = (string) config('services.openrouter.model', 'openrouter/auto');
            $systemPrompt = $this->buildSystemPrompt($emailSoporte, $mensaje, $rules);

            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => (string) config('app.url'),
                    'X-Title' => 'SeaFit',
                ])
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $mensajeOriginal],
                    ],
                    // Parametros conservadores para evitar respuestas inventadas.
                    'temperature' => 0.0,
                    'top_p' => 0.2,
                    'max_tokens' => 220,
                ]);

            if (!$response->ok()) {
                Log::warning('IA chat: respuesta no OK', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'reply' => $this->supportFallbackText($emailSoporte),
                    'source' => 'fallback',
                ]);
            }

            $reply = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            if ($reply === '') {
                return response()->json([
                    'reply' => $this->supportFallbackText($emailSoporte),
                    'source' => 'fallback',
                ]);
            }

            return response()->json([
                'reply' => $reply,
                'source' => 'openrouter',
            ]);
        } catch (\Throwable $e) {
            Log::error('IA chat: excepcion', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => $this->supportFallbackText($emailSoporte),
                'source' => 'fallback',
            ]);
        }
    }

    /**
     * Ejecuta el script Python y lee su salida JSON.
     * Si algo falla, devuelve null para que siga el flujo normal.
     *
     * @param  array<int, array<string, mixed>>  $rules
     * @return array{intent:string,answer:string,confidence:float}|null
     */
    private function answerFromPythonModel(string $mensajeOriginal, array $rules): ?array
    {
        if (!((bool) config('services.ai_chat.python_enabled', true))) {
            return null;
        }

        $pythonBin = trim((string) config('services.ai_chat.python_bin', 'python'));
        $scriptRelative = trim((string) config('services.ai_chat.python_script', 'ai_python/chat_infer.py'));
        $scriptPath = base_path($scriptRelative);
        $timeout = max((int) config('services.ai_chat.python_timeout', 8), 3);
        $minConfidence = (float) config('services.ai_chat.python_min_confidence', 0.58);

        if ($pythonBin === '' || !is_file($scriptPath)) {
            return null;
        }

        // Laravel manda al script el mensaje y reglas en un JSON.
        $payload = json_encode([
            'message' => $mensajeOriginal,
            'rules' => $rules,
            'min_confidence' => $minConfidence,
        ], JSON_UNESCAPED_UNICODE);

        if (!is_string($payload) || $payload === '') {
            return null;
        }

        try {
            $process = new Process([$pythonBin, $scriptPath], base_path(), null, $payload, $timeout);
            $process->run();

            if (!$process->isSuccessful()) {
                return null;
            }

            $output = json_decode($process->getOutput(), true);
            if (!is_array($output) || !((bool) ($output['ok'] ?? false))) {
                return null;
            }

            if (!((bool) ($output['matched'] ?? false))) {
                return null;
            }

            $answer = trim((string) ($output['answer'] ?? ''));
            if ($answer === '') {
                return null;
            }

            return [
                'intent' => trim((string) ($output['intent'] ?? 'python_ml')),
                'answer' => $answer,
                'confidence' => (float) ($output['confidence'] ?? 0.0),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Respuesta por reglas PHP.
     * Solo acepta la mejor regla si supera el umbral minimo.
     *
     * @param  array<int, array<string, mixed>>  $rules
     * @return array{intent:string,answer:string,score:int}|null
     */
    private function answerFromTrainingRules(string $mensaje, array $rules): ?array
    {
        $ranking = $this->rankTrainingRules($mensaje, $rules);
        if ($ranking === []) {
            return null;
        }

        $mejor = $ranking[0];
        $minScore = (int) config('services.ai_chat.min_local_score', 5);

        if ($mejor['score'] < $minScore) {
            return null;
        }

        return $mejor;
    }

    /**
     * Calcula ranking de reglas de mayor a menor.
     * Puntuacion basada en coincidencia de tags y prioridad.
     *
     * @param  array<int, array<string, mixed>>  $rules
     * @return array<int, array{intent:string,answer:string,score:int,priority:int}>
     */
    private function rankTrainingRules(string $mensaje, array $rules): array
    {
        if ($rules === []) {
            return [];
        }

        $tokensMensaje = $this->tokenize($mensaje);
        $ranking = [];

        foreach ($rules as $rule) {
            $tags = $rule['tags'] ?? [];
            $must = $rule['must'] ?? [];
            $avoid = $rule['avoid'] ?? [];
            $answer = trim((string) ($rule['answer'] ?? ''));
            $intent = trim((string) ($rule['intent'] ?? 'sin_intencion'));
            $priority = (int) ($rule['priority'] ?? 0);

            if (!is_array($tags) || $answer === '') {
                continue;
            }

            if (!$this->containsAllTerms($mensaje, $tokensMensaje, is_array($must) ? $must : [])) {
                continue;
            }

            if ($this->containsAnyTerm($mensaje, $tokensMensaje, is_array($avoid) ? $avoid : [])) {
                continue;
            }

            $score = 0;
            $matchedTags = 0;

            foreach ($tags as $tag) {
                $tagNormalizado = $this->normalizarTexto((string) $tag);
                if ($tagNormalizado === '') {
                    continue;
                }

                if (!$this->containsTag($mensaje, $tokensMensaje, $tagNormalizado)) {
                    continue;
                }

                $matchedTags++;
                $tagTokens = $this->tokenize($tagNormalizado);
                $numPalabras = count($tagTokens);

                if ($numPalabras >= 4) {
                    $score += 12;
                } elseif ($numPalabras === 3) {
                    $score += 10;
                } elseif ($numPalabras === 2) {
                    $score += 7;
                } else {
                    $score += 4;
                }

                if ($mensaje === $tagNormalizado) {
                    $score += 20;
                }
            }

            if ($matchedTags === 0) {
                continue;
            }

            $score += max($priority, 0) * 3;

            $ranking[] = [
                'intent' => $intent,
                'answer' => $answer,
                'score' => $score,
                'priority' => $priority,
            ];
        }

        usort($ranking, function (array $a, array $b): int {
            if ($a['score'] === $b['score']) {
                return $b['priority'] <=> $a['priority'];
            }

            return $b['score'] <=> $a['score'];
        });

        return $ranking;
    }

    /**
     * Crea el prompt de OpenRouter usando solo reglas relevantes.
     *
     * @param  array<int, array<string, mixed>>  $rules
     */
    private function buildSystemPrompt(string $emailSoporte, string $mensaje, array $rules): string
    {
        $ranking = $this->rankTrainingRules($mensaje, $rules);
        $lineas = [];

        if ($ranking !== []) {
            $intents = collect($ranking)
                ->take(8)
                ->pluck('intent')
                ->all();

            foreach ($rules as $rule) {
                $intent = trim((string) ($rule['intent'] ?? ''));
                $answer = trim((string) ($rule['answer'] ?? ''));
                if ($intent === '' || $answer === '' || !in_array($intent, $intents, true)) {
                    continue;
                }

                $lineas[] = "- {$answer}";
            }
        }

        if ($lineas === []) {
            foreach ($rules as $rule) {
                $answer = trim((string) ($rule['answer'] ?? ''));
                if ($answer === '') {
                    continue;
                }

                $lineas[] = "- {$answer}";
                if (count($lineas) >= 12) {
                    break;
                }
            }
        }

        $knowledge = implode("\n", $lineas);

        return "Eres el asistente de SeaFit. Responde en espanol, de forma breve y clara.
Usa solo la informacion proporcionada.
No inventes datos ni supongas politicas no indicadas.
Si no tienes informacion suficiente, responde exactamente:
No tengo esa informacion ahora. Puedes contactar en {$emailSoporte}.

Informacion validada de SeaFit:
{$knowledge}";
    }

    /**
     * Devuelve true si todos los terminos de la lista estan presentes.
     */
    private function containsAllTerms(string $mensaje, array $tokensMensaje, array $terms): bool
    {
        foreach ($terms as $term) {
            $termNormalizado = $this->normalizarTexto((string) $term);
            if ($termNormalizado === '') {
                continue;
            }

            if (!$this->containsTag($mensaje, $tokensMensaje, $termNormalizado)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Devuelve true si aparece al menos un termino de la lista.
     */
    private function containsAnyTerm(string $mensaje, array $tokensMensaje, array $terms): bool
    {
        foreach ($terms as $term) {
            $termNormalizado = $this->normalizarTexto((string) $term);
            if ($termNormalizado === '') {
                continue;
            }

            if ($this->containsTag($mensaje, $tokensMensaje, $termNormalizado)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Comprueba si un tag coincide con el mensaje.
     * - Tag de 1 palabra: compara tokens.
     * - Tag de varias palabras: busca frase o coincidencia alta por tokens.
     */
    private function containsTag(string $mensaje, array $tokensMensaje, string $tagNormalizado): bool
    {
        $tagTokens = $this->tokenize($tagNormalizado);
        if ($tagTokens === []) {
            return false;
        }

        if (count($tagTokens) === 1) {
            foreach ($tokensMensaje as $tokenMensaje) {
                if ($this->tokensMatch($tagTokens[0], $tokenMensaje)) {
                    return true;
                }
            }

            return false;
        }

        if (str_contains($mensaje, $tagNormalizado)) {
            return true;
        }

        $tagTokens = $this->filterMeaningfulTokens($tagTokens);
        if ($tagTokens === []) {
            return false;
        }

        $matched = 0;
        foreach ($tagTokens as $tagToken) {
            foreach ($tokensMensaje as $tokenMensaje) {
                if ($this->tokensMatch($tagToken, $tokenMensaje)) {
                    $matched++;
                    break;
                }
            }
        }

        $ratio = $matched / max(count($tagTokens), 1);
        return $matched === count($tagTokens) || ($matched >= 2 && $ratio >= 0.8);
    }

    /**
     * Separa texto en tokens alfanumericos.
     *
     * @return string[]
     */
    private function tokenize(string $text): array
    {
        $parts = preg_split('/[^a-z0-9]+/i', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($parts)) {
            return [];
        }

        return array_values($parts);
    }

    /**
     * Compara dos tokens permitiendo una similitud basica por raiz.
     */
    private function tokensMatch(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $lenA = strlen($a);
        $lenB = strlen($b);

        if ($lenA < 4 || $lenB < 4) {
            return false;
        }

        return substr($a, 0, 5) === substr($b, 0, 5);
    }

    /**
     * Quita palabras poco utiles para mejorar coincidencia por frases.
     *
     * @param  string[]  $tokens
     * @return string[]
     */
    private function filterMeaningfulTokens(array $tokens): array
    {
        $stopwords = [
            'a', 'al', 'de', 'del', 'el', 'la', 'los', 'las', 'y', 'o', 'u',
            'que', 'como', 'con', 'sin', 'en', 'mi', 'tu', 'su', 'se', 'un', 'una',
        ];

        return array_values(array_filter($tokens, function (string $token) use ($stopwords): bool {
            return $token !== '' && !in_array($token, $stopwords, true);
        }));
    }

    /**
     * Normaliza texto para comparar sin problemas de formato.
     */
    private function normalizarTexto(string $text): string
    {
        return (string) Str::of($text)
            ->ascii()
            ->lower()
            ->squish();
    }

    /**
     * Mensaje estandar cuando no hay respuesta fiable.
     */
    private function supportFallbackText(string $emailSoporte): string
    {
        return "No tengo esa informacion ahora. Puedes contactar en {$emailSoporte}.";
    }
}
