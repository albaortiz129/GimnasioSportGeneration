<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Controlador del chat IA de SeaFit.
 * Prioriza respuestas locales definidas en config/entrenar_IA.php
 * y solo usa OpenRouter cuando no hay coincidencias claras.
 */
class AiChatController extends Controller
{
    /**
     * Endpoint principal del chat.
     */
    public function ask(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|min:2|max:500',
        ]);

        $mensajeOriginal = trim((string) $data['message']);
        $mensaje = $this->normalizarTexto($mensajeOriginal);
        $emailSoporte = (string) config('services.ai_chat.support_email', 'soporte.seafit@gmail.com');

        // 1) Respuesta local: rápida, estable y precisa para FAQ conocidas.
        $respuestaLocal = $this->answerFromTrainingRules($mensaje);
        if ($respuestaLocal !== null) {
            return response()->json([
                'reply' => $respuestaLocal['answer'],
                'source' => 'local',
                'intent' => $respuestaLocal['intent'],
            ]);
        }

        // 2) Si no hay API key, se devuelve respuesta de soporte.
        $apiKey = trim((string) config('services.openrouter.api_key'));
        if ($apiKey === '') {
            return response()->json([
                'reply' => "No tengo esa información ahora. Puedes contactar en {$emailSoporte}.",
                'source' => 'fallback',
            ]);
        }

        // 3) Preguntas fuera de reglas locales: se consulta OpenRouter.
        try {
            $baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
            $model = (string) config('services.openrouter.model', 'openrouter/auto');
            $systemPrompt = $this->buildSystemPrompt($emailSoporte, $mensaje);

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
                    // Temperatura baja para minimizar invenciones.
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
                    'reply' => "No tengo esa información ahora. Puedes contactar en {$emailSoporte}.",
                    'source' => 'fallback',
                ]);
            }

            $reply = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
            if ($reply === '') {
                return response()->json([
                    'reply' => "No tengo esa información ahora. Puedes contactar en {$emailSoporte}.",
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
                'reply' => "No tengo esa información ahora. Puedes contactar en {$emailSoporte}.",
                'source' => 'fallback',
            ]);
        }
    }

    /**
     * Intenta responder desde reglas locales de entrenamiento.
     * Devuelve la regla ganadora solo si supera el umbral mínimo.
     *
     * @return array{intent:string,answer:string,score:int}|null
     */
    private function answerFromTrainingRules(string $mensaje): ?array
    {
        $ranking = $this->rankTrainingRules($mensaje);
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
     * Devuelve reglas ordenadas de mayor a menor precisión para el mensaje.
     *
     * @return array<int, array{intent:string,answer:string,score:int,priority:int}>
     */
    private function rankTrainingRules(string $mensaje): array
    {
        $rules = config('entrenar_IA.rules', []);
        if (!is_array($rules) || $rules === []) {
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

                // Cuanto más específica sea la frase, más peso tiene.
                if ($numPalabras >= 4) {
                    $score += 12;
                } elseif ($numPalabras === 3) {
                    $score += 10;
                } elseif ($numPalabras === 2) {
                    $score += 7;
                } else {
                    $score += 4;
                }

                // Coincidencia exacta de frase completa: máxima confianza.
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
     * Crea el prompt para OpenRouter usando reglas relevantes.
     * Así reducimos ruido y mantenemos respuestas más precisas.
     */
    private function buildSystemPrompt(string $emailSoporte, string $mensaje): string
    {
        $ranking = $this->rankTrainingRules($mensaje);
        $rules = config('entrenar_IA.rules', []);
        $lineas = [];

        // Si hay coincidencias parciales, pasamos solo las más cercanas.
        if ($ranking !== []) {
            $intents = collect($ranking)
                ->take(8)
                ->pluck('intent')
                ->all();

            foreach ((array) $rules as $rule) {
                $intent = trim((string) ($rule['intent'] ?? ''));
                $answer = trim((string) ($rule['answer'] ?? ''));
                if ($intent === '' || $answer === '' || !in_array($intent, $intents, true)) {
                    continue;
                }

                $lineas[] = "- {$answer}";
            }
        }

        // Si no hay ranking útil, pasamos una base corta general.
        if ($lineas === []) {
            foreach ((array) $rules as $rule) {
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

        return "Eres el asistente de SeaFit. Responde en español, de forma breve y clara.
Usa solo la información proporcionada.
No inventes datos ni supongas políticas no indicadas.
Si no tienes información suficiente, responde exactamente:
No tengo esa información ahora. Puedes contactar en {$emailSoporte}.

Información validada de SeaFit:
{$knowledge}";
    }

    /**
     * Comprueba que todos los términos estén presentes.
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
     * Comprueba si existe al menos un término de una lista.
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
     * Comprueba coincidencia de tag:
     * - Si es frase: búsqueda por subcadena o por tokens similares.
     * - Si es una palabra: búsqueda por token.
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

        // Requiere coincidencia total o casi total para evitar falsos positivos.
        $ratio = $matched / max(count($tagTokens), 1);
        return $matched === count($tagTokens) || ($matched >= 2 && $ratio >= 0.8);
    }

    /**
     * Tokeniza texto normalizado para comparar palabras completas.
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
     * Compara dos tokens permitiendo ligeras variaciones.
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

        // Coincidencia por raíz aproximada para cubrir variantes comunes.
        return substr($a, 0, 5) === substr($b, 0, 5);
    }

    /**
     * Filtra palabras poco informativas para mejorar el matching por frases.
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
     * Normaliza texto para comparaciones robustas.
     */
    private function normalizarTexto(string $text): string
    {
        return (string) Str::of($text)
            ->ascii()
            ->lower()
            ->squish();
    }
}
