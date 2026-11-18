# Beheerder-handleiding

Welkom als beheerder van *Ontmoet de Oudegracht*. Dit document legt
stap voor stap uit hoe je de inhoud van de website aanpast.

## Inloggen

1. Ga naar <http://localhost:8080/cms/> (lokaal) of de productie-URL.
2. Vul je e-mailadres en wachtwoord in.
3. Standaard credentials (lokaal): `admin@hua.local` / `admin`.
   **Verander dit wachtwoord direct na de eerste installatie.**

## Dashboard

Op het dashboard zie je hoeveel locaties en archiefstukken er zijn.
De twee groene knoppen brengen je direct naar het aanmaken van iets
nieuws.

## Locatie toevoegen of aanpassen

Een **locatie** is een plek langs de Oudegracht die in de film in beeld
komt. Elke locatie heeft:

| Veld | Uitleg |
|---|---|
| Slug | Korte url-vriendelijke naam, bv. `domtoren`. Alleen kleine letters, cijfers en streepjes. |
| Sorteervolgorde | Bepaalt de volgorde op de tijdlijn (laag = eerst). |
| Video start (sec) | Op welke seconde in de video wordt de plek getoond? |
| Video eind (sec) | Op welke seconde is hij voor het laatst in beeld? |
| Kaart X (%) | Horizontale positie op de plattegrond (0 = links, 100 = rechts). |
| Kaart Y (%) | Verticale positie (0 = boven, 100 = onder). |
| Naam / Name | Zichtbare naam in NL en EN. |
| Korte beschrijving | Enkele zinnen die onder de titel in de modal verschijnen. B1-taalniveau! |

> **Tip voor X/Y**: open de homepage, klik met rechts op de plattegrond
> en kies "Inspecteren" om de pixelpositie te zien. Reken dat om naar %.

## Archiefstuk toevoegen

Archiefstukken zijn de historische beelden/foto's die bij een locatie
horen. Per locatie kun je er meerdere maken; ze verschijnen in de modal
wanneer een bezoeker op een locatie klikt.

1. Klik op *Archiefstukken* → *Nieuw archiefstuk*.
2. Kies een locatie waar het stuk bij hoort.
3. Upload een afbeelding (JPEG/PNG/WebP, max 5 MB).
4. Vul jaar in (optioneel).
5. Vul titel + beschrijving in in **beide** talen.
6. Klik *Opslaan*.

## Verwijderen

Elke rij in de lijst-pagina's heeft een rode *Verwijderen* knop. Je krijgt
een bevestiging voordat de actie doorgevoerd wordt. **Let op**: als je
een locatie verwijdert, gaan alle gekoppelde archiefstukken ook weg.

## Uitloggen

Rechtsboven in het CMS staat *Uitloggen*. Doe dit altijd als je klaar bent,
zeker op een gedeelde computer.
