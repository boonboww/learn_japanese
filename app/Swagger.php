<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Learn Japanese API Documentation",
    version: "1.0.0",
    description: "API endpoints for Learn Japanese application"
)]
#[OA\Server(
    url: "https://learn-japanese-0464.onrender.com/api",
    description: "Production Server"
)]
#[OA\Server(
    url: "http://127.0.0.1:8081/api",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "Authorization",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT"
)]

// --- SCHEMAS ---

#[OA\Schema(
    schema: "User",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "username", type: "string", example: "Test User"),
        new OA\Property(property: "email", type: "string", example: "test@example.com"),
        new OA\Property(property: "avatar", type: "string", nullable: true, example: "https://example.com/avatar.png"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]

#[OA\Schema(
    schema: "Alphabet",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "group_id", type: "integer", example: 1),
        new OA\Property(property: "character", type: "string", example: "あ"),
        new OA\Property(property: "romaji", type: "string", example: "a"),
        new OA\Property(property: "type", type: "string", example: "hiragana"),
        new OA\Property(property: "order_index", type: "integer", example: 1)
    ]
)]

#[OA\Schema(
    schema: "AlphabetGroup",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "A-Row (あいうえお)"),
        new OA\Property(property: "type", type: "string", example: "hiragana"),
        new OA\Property(property: "order_index", type: "integer", example: 1),
        new OA\Property(
            property: "alphabets",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/Alphabet")
        )
    ]
)]

#[OA\Schema(
    schema: "Vocabulary",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "group_id", type: "integer", example: 1),
        new OA\Property(property: "word", type: "string", example: "一つ"),
        new OA\Property(property: "furigana", type: "string", nullable: true, example: "ひとつ"),
        new OA\Property(property: "romaji", type: "string", example: "hitotsu"),
        new OA\Property(property: "meaning", type: "string", example: "Một cái"),
        new OA\Property(property: "type", type: "string", nullable: true, example: "numbers_counters")
    ]
)]

#[OA\Schema(
    schema: "Kanji",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "character", type: "string", example: "一"),
        new OA\Property(property: "meaning", type: "string", example: "Một"),
        new OA\Property(property: "onyomi", type: "string", nullable: true, example: "イチ"),
        new OA\Property(property: "kunyomi", type: "string", nullable: true, example: "ひと"),
        new OA\Property(property: "stroke_path", type: "string", nullable: true, example: "kanji/1.svg")
    ]
)]
class Swagger
{
}
