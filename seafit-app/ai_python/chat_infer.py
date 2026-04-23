#!/usr/bin/env python3
"""
Motor de IA local para el chat de SeaFit (version sencilla).

Idea general:
1) Laravel le pasa un mensaje y las reglas (preguntas/respuestas).
2) Este script entrena un modelo pequeno en memoria.
3) Devuelve la mejor respuesta SOLO si la confianza es suficiente.
4) Si no hay confianza, devuelve "matched: false" para que Laravel
   pruebe con su siguiente capa (reglas PHP u OpenRouter).

Entrada por STDIN (JSON):
{
  "message": "...",
  "rules": [...],
  "min_confidence": 0.58
}

Salida por STDOUT (JSON):
{
  "ok": true,
  "matched": true|false,
  "intent": "...",
  "answer": "...",
  "confidence": 0.0-1.0
}
"""

from __future__ import annotations

import json
import re
import sys
import unicodedata
from typing import Any


def normalize_text(text: str) -> str:
    """
    Normaliza texto para comparar mejor:
    - quita tildes y simbolos raros
    - pasa a minusculas
    - limpia espacios dobles
    """
    if not isinstance(text, str):
        return ""

    ascii_text = (
        unicodedata.normalize("NFKD", text)
        .encode("ascii", "ignore")
        .decode("ascii")
    )
    ascii_text = ascii_text.lower().strip()
    return re.sub(r"\s+", " ", ascii_text)


def tokenize(text: str) -> list[str]:
    """Divide texto en palabras simples (solo letras y numeros)."""
    if not text:
        return []
    return [t for t in re.split(r"[^a-z0-9]+", text) if t]


def terms_present(message_norm: str, terms: list[str]) -> bool:
    """Comprueba que todos los terminos obligatorios (must) aparezcan."""
    for term in terms:
        term_norm = normalize_text(term)
        if term_norm and term_norm not in message_norm:
            return False
    return True


def has_avoided_terms(message_norm: str, terms: list[str]) -> bool:
    """Comprueba si aparece algun termino prohibido (avoid)."""
    for term in terms:
        term_norm = normalize_text(term)
        if term_norm and term_norm in message_norm:
            return True
    return False


def keyword_overlap(message_norm: str, tags: list[str]) -> float:
    """
    Calcula una puntuacion extra por palabras en comun.
    Sirve para ajustar la confianza del modelo.
    """
    msg_tokens = set(tokenize(message_norm))
    if not msg_tokens:
        return 0.0

    best = 0.0
    for tag in tags:
        tag_tokens = set(tokenize(normalize_text(tag)))
        if not tag_tokens:
            continue

        covered = sum(1 for token in tag_tokens if token in msg_tokens)
        ratio = covered / max(len(tag_tokens), 1)
        if ratio > best:
            best = ratio

    return best


def build_training_set(rules: list[dict[str, Any]]) -> tuple[list[str], list[str]]:
    """
    Construye dataset:
    - texts: frases de ejemplo (tags)
    - labels: intencion de cada frase

    Nota:
    Si una regla tiene mas prioridad, repetimos sus tags para darle mas peso.
    """
    texts: list[str] = []
    labels: list[str] = []

    for rule in rules:
        intent = str(rule.get("intent", "")).strip()
        tags = rule.get("tags", [])
        priority = int(rule.get("priority", 0) or 0)

        if not intent or not isinstance(tags, list):
            continue

        repeat = 1 + max(priority, 0) // 3

        for raw_tag in tags:
            tag = normalize_text(str(raw_tag))
            if not tag:
                continue

            for _ in range(repeat):
                texts.append(tag)
                labels.append(intent)

    return texts, labels


def pick_rule_by_intent(
    rules: list[dict[str, Any]], intent: str, message_norm: str
) -> dict[str, Any] | None:
    """
    Busca la mejor regla para una intencion concreta.
    Filtra por:
    - must (debe contener)
    - avoid (no debe contener)
    Luego elige la de mayor prioridad.
    """
    candidates: list[dict[str, Any]] = []

    for rule in rules:
        if str(rule.get("intent", "")).strip() != intent:
            continue

        must = rule.get("must", [])
        avoid = rule.get("avoid", [])
        if not isinstance(must, list):
            must = []
        if not isinstance(avoid, list):
            avoid = []

        if not terms_present(message_norm, must):
            continue
        if has_avoided_terms(message_norm, avoid):
            continue

        candidates.append(rule)

    if not candidates:
        return None

    candidates.sort(key=lambda r: int(r.get("priority", 0) or 0), reverse=True)
    return candidates[0]


def main() -> int:
    # 1) Importamos scikit-learn. Si falta, no rompemos nada.
    try:
        from sklearn.feature_extraction.text import TfidfVectorizer
        from sklearn.linear_model import LogisticRegression
        from sklearn.pipeline import make_pipeline
    except Exception as exc:  # noqa: BLE001
        print(
            json.dumps(
                {
                    "ok": False,
                    "matched": False,
                    "error": f"scikit-learn no disponible: {exc}",
                },
                ensure_ascii=False,
            )
        )
        return 0

    # 2) Leemos JSON de entrada.
    try:
        payload = json.loads(sys.stdin.read() or "{}")
    except Exception:  # noqa: BLE001
        payload = {}

    message = str(payload.get("message", "")).strip()
    rules = payload.get("rules", [])
    min_confidence = float(payload.get("min_confidence", 0.58))

    # 3) Si no hay datos suficientes, devolvemos "no match".
    if not message or not isinstance(rules, list) or not rules:
        print(json.dumps({"ok": True, "matched": False}, ensure_ascii=False))
        return 0

    texts, labels = build_training_set(rules)
    if len(texts) < 4 or len(set(labels)) < 2:
        print(json.dumps({"ok": True, "matched": False}, ensure_ascii=False))
        return 0

    # 4) Entrenamos el modelo en memoria.
    try:
        model = make_pipeline(
            TfidfVectorizer(ngram_range=(1, 2)),
            LogisticRegression(max_iter=2000, class_weight="balanced", C=4.0),
        )
        model.fit(texts, labels)
    except Exception as exc:  # noqa: BLE001
        print(
            json.dumps(
                {"ok": False, "matched": False, "error": f"entrenamiento fallo: {exc}"},
                ensure_ascii=False,
            )
        )
        return 0

    # 5) Predecimos probabilidades por intencion.
    message_norm = normalize_text(message)
    try:
        probabilities = model.predict_proba([message_norm])[0]
        classifier = model.named_steps.get("logisticregression")
        classes = list(classifier.classes_) if classifier is not None else []
    except Exception as exc:  # noqa: BLE001
        print(
            json.dumps(
                {"ok": False, "matched": False, "error": f"inferencia fallo: {exc}"},
                ensure_ascii=False,
            )
        )
        return 0

    if not classes:
        print(json.dumps({"ok": True, "matched": False}, ensure_ascii=False))
        return 0

    # 6) Probamos intenciones de mayor a menor confianza.
    ranked_indexes = sorted(
        range(len(probabilities)),
        key=lambda idx: float(probabilities[idx]),
        reverse=True,
    )

    for idx in ranked_indexes:
        intent = str(classes[idx]).strip()
        base_confidence = float(probabilities[idx])
        if not intent:
            continue

        # Regla final segun must/avoid/priority.
        rule = pick_rule_by_intent(rules, intent, message_norm)
        if not rule:
            continue

        answer = str(rule.get("answer", "")).strip()
        tags = rule.get("tags", [])
        if not answer or not isinstance(tags, list):
            continue

        # Confianza final = modelo + coincidencia por palabras.
        overlap = keyword_overlap(message_norm, [str(t) for t in tags])
        final_confidence = (base_confidence * 0.7) + (overlap * 0.3)

        if final_confidence < min_confidence:
            continue

        print(
            json.dumps(
                {
                    "ok": True,
                    "matched": True,
                    "intent": intent,
                    "answer": answer,
                    "confidence": round(final_confidence, 4),
                },
                ensure_ascii=False,
            )
        )
        return 0

    # 7) Si no alcanza confianza minima, no respondemos automaticamente.
    print(json.dumps({"ok": True, "matched": False}, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
