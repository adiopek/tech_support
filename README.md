# Instrukcja uruchomienia

## Wymagania
- Docker
- Docker Compose
- make

## Uruchomienie
1. Sklonowanie repozytorium.
2. `make init`.
3. Dokumentacja API (Swagger UI) będzie dostępna pod `http://localhost:8000/api`.

## Produkcja
Aby uruchomić aplikację w trybie produkcyjnym:
1. Skopiuj `.env.local.dist` do `.env.local`.
2. Uzupełnij hasła i sekrety w pliku `.env.local`.
3. Uruchom kontenery: `docker compose -f docker-compose.prod.yml up -d`.

## Użytkownicy
- **Admin**: `admin@example.com` / `admin123` / `admin-token` (do autoryzacji w Swagger UI)
- **Technik**: `tech@example.com` / `tech123` / `tech-token` (do autoryzacji w Swagger UI)
