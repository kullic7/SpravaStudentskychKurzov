# Správa študentských kurzov

Webová aplikácia vytvorená v PHP v architektúre MVC.
Aplikácia slúži na správu používateľov, kurzov a zápisov študentov.

---

## Požiadavky

Pre spustenie aplikácie je potrebné mať nainštalované:

- Docker
- Docker Compose

---

## Inštalácia a spustenie

1. Naklonovanie repozitára:
```bash
git clone <URL_REPOZITARA>
cd <NAZOV_PROJEKTU>
```
2. Vytvorenie súboru .env v koreňovom adresári projektu:
```bash
POSTGRES_USER=app
POSTGRES_PASSWORD=secret
POSTGRES_DB=kurzy
```

3. Spustenie aplikácie pomocou Docker Compose:
```bash
docker compose up --build
```
4. Prístup k aplikácii
```
Webová aplikácia:
http://localhost

Adminer (správa databázy):
http://localhost:8080
```

## Databáza: PostgreSQL

Server: postgres

Port: 5432

Používateľské údaje sú definované v súbore .env

Databázové tabuľky sa vytvárajú automaticky pri prvom spustení
pomocou SQL skriptov v priečinku sql/.

## Testovacie prihlasovacie údaje

Pre jednoduché vyskúšanie aplikácie sú k dispozícii nasledujúce účty:

### Admin

Email: admin@example.com

Heslo: admin123

### Študent

Email: john.doe@example.com

Heslo: password123

### Učiteľ

Email: peter.novak@example.com

Heslo: password123