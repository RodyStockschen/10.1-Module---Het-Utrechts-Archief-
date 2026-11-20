SET NAMES utf8mb4;

-- Singleton settings-rij (minimap focus-gebied: de route van de Gaardbrug tot de Stadhuisbrug)
INSERT INTO settings (id, minimap_focus_x, minimap_focus_y, minimap_focus_zoom) VALUES (1, 56.50, 51.55, 12.00);

-- Admin user: admin@hua.local / admin (verander dit na installatie!)
INSERT INTO admins (email, password_hash) VALUES
('admin@hua.local', '$2y$12$PVDrEHVAh8/LoTdEUk3j6OF9EvkQIecTt9CN4zP8LiC9XV7J8fiMu');

-- Locaties en archiefstukken uit "Bronmateriaal_en_info.pdf" van Het Utrechts Archief.
-- Route van de film: net voor de Gaardbrug tot onder de Stadhuisbrug.
-- short_description = korte kindertekst, archive_text = originele tekst van het archief.
-- Café De Postillon en Lijnmarkt/Lichte Gaard hebben in het bronmateriaal alleen foto's.
-- 'Start van de route' (id 9, sort_order 0 = eerste) is het kopje "Start:" uit het
-- bronmateriaal: een eigen pop-up aan het begin van de film, zonder foto's. De stip
-- staat net voor de Hamburgerbrug, de eerste brug in de film (vóór de Gaardbrug).

INSERT INTO locations (id, slug, video_start_sec, video_end_sec, map_x_pct, map_y_pct, sort_order) VALUES
(9, 'start',                   0,  14, 57.80, 54.78, 0),
(1, 'gaardbrug',              14,  58, 57.45, 53.45, 1),
(2, 'werven-en-werfkelders',  58,  65, 57.38, 53.12, 2),
(3, 'cafe-de-postillon',      65,  72, 57.22, 52.85, 3),
(4, 'lijnmarkt-lichte-gaard', 72,  82, 56.95, 52.40, 4),
(5, 'maartensbrug',           82, 110, 56.42, 51.43, 5),
(6, 'vismarkt',              110, 118, 56.25, 50.95, 6),
(7, 'kalisbrug',             118, 134, 56.02, 50.49, 7),
(8, 'stadhuisbrug',          134, 182, 55.55, 49.62, 8);

INSERT INTO location_i18n (location_id, lang, name, short_description, archive_text) VALUES
(9, 'nl', 'Start van de route',
   'Hier begint de boottocht over de Oudegracht! Je vaart onder oude bruggen door, langs huizen van handelaren en kelders aan het water. Onderweg zie je rode labels in de film. Klik erop en ontdek meer over die plek.',
   'Tussen de Gaardbrug en de Bezembrug ligt een bijzonder stuk van de Utrechtse Oudegracht. Je vindt er historische bruggen, oude handelshuizen, werfkelders en sporen van het middeleeuwse handelsleven. Een aantal plekken worden tijdens het boottochtje uitgelicht.'),
(9, 'en', 'Start of the route',
   'This is where the boat trip along the Oudegracht begins! You sail under old bridges, past merchants’ houses and cellars by the water. Along the way you will see red labels in the film. Click one to discover more about that place.',
   'Between the Gaardbrug and the Bezembrug lies a special stretch of Utrecht’s Oudegracht. You will find historic bridges, old merchant houses, wharf cellars and traces of medieval trade. A number of places are highlighted during the boat trip.'),
(1, 'nl', 'Gaardbrug',
   'Vroeger heette een tuin een ‘gaard’. Deze brug is genoemd naar de tuin van de bisschop. Vanaf de brug zie je de Domtoren.',
   'De Gaardbrug is een van de bruggen over de Utrechtse Oudegracht en ontleent haar naam aan de vroegere bisschoppelijke tuin, die ook wel de ‘gaard’ werd genoemd. Vanaf de brug is er zicht op de Oudegracht en de Domtoren, dit maakt het een populaire plek om foto’s te maken.'),
(1, 'en', 'Gaardbrug',
   'Long ago, a garden was called a ‘gaard’. This bridge is named after the bishop’s garden. From the bridge you can see the Dom Tower.',
   'The Gaardbrug is one of the bridges over Utrecht’s Oudegracht and takes its name from the former bishop’s garden, which was also called the ‘gaard’. The bridge offers a view of the Oudegracht and the Dom Tower, which makes it a popular spot for taking photos.'),

(2, 'nl', 'Werven en werfkelders',
   'Zie je de deuren onder de straat? Dat zijn werfkelders. Vroeger brachten schepen hier spullen, en handelaren bewaarden die in de kelders.',
   'Langs de gracht zie je de bijzondere combinatie van een hoger gelegen straat en een lager gelegen werf aan het water. Handelaren gebruikten de werfkelders om goederen op te slaan en rechtstreeks vanaf schepen aan te voeren. Zo groeide de Oudegracht uit tot een belangrijke handelsroute door de stad.'),
(2, 'en', 'Wharves and wharf cellars',
   'Can you see the doors below the street? Those are wharf cellars. Long ago, boats brought goods here, and traders stored them in the cellars.',
   'Along the canal you can see the special combination of a street higher up and a wharf lower down by the water. Traders used the wharf cellars to store goods and to bring them in directly from boats. This is how the Oudegracht grew into an important trade route through the city.'),

(3, 'nl', 'Café De Postillon', '', ''),
(3, 'en', 'Café De Postillon', '', ''),

(4, 'nl', 'Lijnmarkt en Lichte Gaard', '', ''),
(4, 'en', 'Lijnmarkt and Lichte Gaard', '', ''),

(5, 'nl', 'Maartensbrug',
   'Dit is de oudste brug over de Oudegracht. De eerste stenen brug is al meer dan 600 jaar oud! Later werd hij breder, zodat de paardentram (een tram die door paarden werd getrokken) erover kon.',
   'De Maartensbrug is de oudste brug over de Utrechtse Oudegracht. De brug werd oorspronkelijk de Borchbrug genoemd, omdat hij toegang gaf tot de bisschoppelijke burcht op de plek van het huidige Domplein. De eerste stenen brug werd in 1404 gebouwd en in 1906 verbreed om ruimte te maken voor het toenemende verkeer en de paardentram. De huidige naam verwijst naar Sint-Maarten, de beschermheilige van Utrecht.'),
(5, 'en', 'Maartensbrug',
   'This is the oldest bridge over the Oudegracht. The first stone bridge is more than 600 years old! Later it was made wider, so the horse tram (a tram pulled by horses) could cross it.',
   'The Maartensbrug is the oldest bridge over Utrecht’s Oudegracht. The bridge was originally called the Borchbrug, because it gave access to the bishop’s castle on the site of today’s Domplein. The first stone bridge was built in 1404 and widened in 1906 to make room for the growing traffic and the horse tram. Its current name refers to Saint Martin, the patron saint of Utrecht.'),

(6, 'nl', 'Vismarkt',
   'Dit is de Vismarkt, de oudste markt van Utrecht. Hier verkopen mensen al honderden jaren vis.',
   'De Vismarkt is de oudste markt van Utrecht. Al eeuwenlang wordt op het gedeelte van de Oudegracht tussen de Stadhuisbrug en de Maartensbrug vis verhandeld. Dit deel van de gracht ontleent zijn naam dan ook aan de markt.'),
(6, 'en', 'Vismarkt (Fish Market)',
   'This is the Vismarkt (Fish Market), the oldest market in Utrecht. People have been selling fish here for hundreds of years.',
   'The Vismarkt (Fish Market) is the oldest market in Utrecht. For centuries, fish has been traded on the part of the Oudegracht between the Stadhuisbrug and the Maartensbrug. This part of the canal therefore takes its name from the market.'),

(7, 'nl', 'Kalisbrug',
   'Op deze brug werd vroeger vis verkocht. Zolang de vis nog niet verkocht was, bleef hij vers in manden die in het water hingen.',
   'De Kalisbrug, ook wel Visbrug genoemd, vormde een belangrijk onderdeel van de Vismarkt en diende als visafslag. Wanneer er voldoende vis was aangevoerd, kondigde de stadsomroeper de verkoop aan. De vis werd vers gehouden in viskaren die in de gracht hingen en verkocht vanaf visbanken op de brug. De traditionele vismarkt verdween aan het begin van de 20e eeuw, maar in de winkels aan de Vismarkt bleven nog lange tijd visverkopers actief. De laatste traditionele viswinkel sloot in 1970.'),
(7, 'en', 'Kalisbrug',
   'Fish used to be sold on this bridge. Until it was sold, the fish stayed fresh in baskets hanging in the water.',
   'The Kalisbrug, also called the Visbrug (Fish Bridge), was an important part of the Vismarkt and served as a fish auction. When enough fish had been brought in, the town crier announced the sale. The fish was kept fresh in fish crates hanging in the canal and sold from fish stalls on the bridge. The traditional fish market disappeared at the beginning of the 20th century, but fishmongers stayed active in the shops on the Vismarkt for a long time. The last traditional fish shop closed in 1970.'),

(8, 'nl', 'Stadhuisbrug en stadhuis',
   'Naast deze brug staat het stadhuis. Daar werkt het bestuur van de stad. De brug is zo breed dat je eronder door een soort tunnel vaart.',
   'De Stadhuisbrug dankt haar naam aan het Utrechtse stadhuis dat direct aan de brug ligt. Op deze plek zetelde het stadsbestuur al vanaf de 14e eeuw. De brug ontstond in de 16e eeuw, toen twee bestaande bruggen, de Broodbrug en de Huidenbrug, met elkaar werden verbonden en overkluisd. Het plein dat hierdoor ontstond, werd gebruikt voor openbare afkondigingen van besluiten van het stadsbestuur en kreeg later de naam Stadhuisbrug.'),
(8, 'en', 'Stadhuisbrug and City Hall',
   'City Hall stands right next to this bridge. That is where the city council works. The bridge is so wide that you sail underneath it as if through a tunnel.',
   'The Stadhuisbrug owes its name to Utrecht’s City Hall, which stands right next to the bridge. The city council has been based here since the 14th century. The bridge was created in the 16th century, when two existing bridges, the Broodbrug and the Huidenbrug, were joined together and vaulted over. The square that this created was used for public announcements of the city council’s decisions and was later named the Stadhuisbrug.');

-- Archiefstukken: maximaal drie foto's per plek (keuze op basis van wat het best bij de filmbeelden past),
-- per plek van oud naar nieuw. credit = bronvermelding (licentie vraagt naamsvermelding van de maker).
INSERT INTO archive_items (id, location_id, image_path, year, credit, source_url, sort_order) VALUES
(1,  1, 'assets/uploads/hua-22122.jpg',  1961, 'Het Utrechts Archief (22122) · C. Stevens · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/0b35692a-2efe-5c49-924a-b82e3b6fea43', 1),
(2,  1, 'assets/uploads/hua-903109.jpg', 2022, 'Het Utrechts Archief (903109) · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/e154e77c-bae1-bbad-e053-8f04000a554f', 2),
(3,  2, 'assets/uploads/hua-67316.jpg',  1974, 'Het Utrechts Archief (67316) · Gemeentearchief Utrecht · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/17304feb-14dd-50b5-80b5-2031dbdae08c', 1),
(4,  3, 'assets/uploads/hua-906087.jpg', 2012, 'Het Utrechts Archief (906087) · D.C. Goosen · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/ff48325f-f026-95b5-e053-8f04000a4889', 1),
(5,  4, 'assets/uploads/hua-1587.jpg',   1915, 'Het Utrechts Archief (1587) · Publiek domein', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/74208d55-8e53-5b50-ba7c-6690258d82bd', 1),
(6,  4, 'assets/uploads/hua-22275.jpg',  1966, 'Het Utrechts Archief (22275) · H. Kruythof · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/e7b5ae82-6b10-5025-ba2d-34396da909a1', 2),
(7,  4, 'assets/uploads/hua-835793.jpg', 1976, 'Het Utrechts Archief (835793) · Fotodienst GAU · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/91458518-5317-b66c-e053-4701000a4ffb', 3),
(8,  5, 'assets/uploads/hua-406922.jpg', 1960, 'Het Utrechts Archief (406922) · S. Budde · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/b2bd88e3-de5b-2173-e053-4701000a486f', 1),
(9,  5, 'assets/uploads/hua-822378.jpg', 1993, 'Het Utrechts Archief (822378) · Fotodienst GAU · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/c744769e-0725-55d4-9e2e-03e92dbcb604', 2),
(10, 5, 'assets/uploads/hua-822375.jpg', 1993, 'Het Utrechts Archief (822375) · Fotodienst GAU · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/62665b8e-a252-5ec3-a01e-da19075cf809', 3),
(11, 6, 'assets/uploads/hua-816418.jpg', NULL, 'Het Utrechts Archief (816418) · F.F. van der Werf · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/72dd2dcb-9bac-546f-bbbc-f5c1111cc735', 1),
(12, 7, 'assets/uploads/hua-603347.jpg', 1873, 'Het Utrechts Archief (603347) · W.C. van Dijk · Publiek domein', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/452c4ef2-e0f1-5eda-9aca-df86f8e57f51', 1),
(13, 7, 'assets/uploads/hua-852310.jpg', 1975, 'Het Utrechts Archief (852310) · Euro Color Cards / 1000 Foto’s · CC BY-NC 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/b92f162a-1fab-12c0-e053-4701000a2676', 2),
(14, 7, 'assets/uploads/hua-852298.jpg', 1975, 'Het Utrechts Archief (852298) · Euro Color Cards / 1000 Foto’s · CC BY-NC 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/b92c390a-7bf7-cb12-e053-4701000ab712', 3),
(15, 8, 'assets/uploads/hua-856849.jpg', 1965, 'Het Utrechts Archief (856849) · J.M. Vermeulen · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/db73d181-f47b-bae0-e053-8f04000ac07c', 1),
(16, 8, 'assets/uploads/hua-852288.jpg', 1975, 'Het Utrechts Archief (852288) · Euro Color Cards / 1000 Foto’s · CC BY-NC 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/b92bb223-d895-18e0-e053-4701000a7b09', 2),
(17, 8, 'assets/uploads/hua-83650.jpg',  1976, 'Het Utrechts Archief (83650) · Fotodienst GAU · CC BY 4.0', 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/aa8056d4-1ab7-5229-b8d8-5a70192c714d', 3);

INSERT INTO archive_i18n (archive_item_id, lang, title, description) VALUES
(1,  'nl', 'Doorkijk onder de Gaardbrug', 'Doorkijk onder de boog van de Gaardbrug over de Oudegracht te Utrecht, met op de achtergrond de Maartensbrug en rechts de Donkere Gaard.'),
(1,  'en', 'View under the Gaardbrug', 'View under the arch of the Gaardbrug over the Oudegracht in Utrecht, with the Maartensbrug in the background and the Donkere Gaard on the right.'),
(2,  'nl', 'Uitzicht vanaf de Gaardbrug', 'Gezicht op de Oudegracht te Utrecht, vanaf de Gaardbrug, met links de achtergevels van de panden aan de Lijnmarkt en rechts de Donkere Gaard.'),
(2,  'en', 'View from the Gaardbrug', 'View of the Oudegracht in Utrecht from the Gaardbrug, with the rear façades of the buildings on the Lijnmarkt on the left and the Donkere Gaard on the right.'),
(3,  'nl', 'Werfkelder bij de Gaardbrug', 'Gezicht op de werfkelder ter hoogte van het pand Oudegracht 190 te Utrecht, met links een gedeelte van de Gaardbrug.'),
(3,  'en', 'Wharf cellar near the Gaardbrug', 'View of the wharf cellar near Oudegracht 190 in Utrecht, with part of the Gaardbrug on the left.'),
(4,  'nl', 'Café De Postillon', 'Gezicht op de voorgevel van het pand Lijnmarkt 50 (Café de Postillon) bij de Gaardbrug te Utrecht.'),
(4,  'en', 'Café De Postillon', 'View of the front of Lijnmarkt 50 (Café de Postillon) near the Gaardbrug in Utrecht.'),
(5,  'nl', 'Ansichtkaart van de Oudegracht', 'Gezicht op de Oudegracht te Utrecht met op de achtergrond de Maartensbrug; links de achtergevels van enkele huizen aan de Lijnmarkt, rechts de achtergevels van de huizen Donkere Gaard 1-13. Ingekleurde ansichtkaart.'),
(5,  'en', 'Postcard of the Oudegracht', 'View of the Oudegracht in Utrecht with the Maartensbrug in the background; on the left the rear façades of some houses on the Lijnmarkt, on the right the rear façades of the houses at Donkere Gaard 1-13. Hand-coloured postcard.'),
(6,  'nl', 'Huizen aan de Lichte Gaard', 'Gezicht op de voorgevels van de panden Lichte Gaard 8-lager te Utrecht met op de achtergrond de Maartensbrug over de Oudegracht en de Vismarkt.'),
(6,  'en', 'Houses on the Lichte Gaard', 'View of the fronts of the buildings at Lichte Gaard 8 and lower in Utrecht, with the Maartensbrug over the Oudegracht and the Vismarkt in the background.'),
(7,  'nl', 'Oudegracht bij de Lichte Gaard', 'Gezicht over de Oudegracht te Utrecht met rechts de Lichte Gaard, in het midden de Maartensbrug en links de achtergevels van enkele huizen aan de Lijnmarkt.'),
(7,  'en', 'Oudegracht at the Lichte Gaard', 'View across the Oudegracht in Utrecht with the Lichte Gaard on the right, the Maartensbrug in the middle and the rear façades of some houses on the Lijnmarkt on the left.'),
(8,  'nl', 'Schuit bij de Maartensbrug', 'Een schuit met kratten in de Oudegracht te Utrecht, ter hoogte van de Maartensbrug.'),
(8,  'en', 'Barge at the Maartensbrug', 'A barge loaded with crates in the Oudegracht in Utrecht, near the Maartensbrug.'),
(9,  'nl', 'De Maartensbrug vanaf de Gaardbrug', 'Gezicht op de Maartensbrug over de Oudegracht te Utrecht vanaf de Gaardbrug; op de achtergrond het Stadhuis.'),
(9,  'en', 'The Maartensbrug from the Gaardbrug', 'View of the Maartensbrug over the Oudegracht in Utrecht from the Gaardbrug; City Hall in the background.'),
(10, 'nl', 'De bogen van de Maartensbrug', 'Gezicht op de Maartensbrug over de Oudegracht te Utrecht, vanaf de werf onder de Lichte Gaard; op de achtergrond de achtergevels van enkele panden aan de Choorstraat.'),
(10, 'en', 'The arches of the Maartensbrug', 'View of the Maartensbrug over the Oudegracht in Utrecht from the wharf below the Lichte Gaard; in the background the rear façades of some buildings on the Choorstraat.'),
(11, 'nl', 'Kelder aan de Zoutmarkt', 'De restauratie van de kelder aan de Zoutmarkt achter het pand Choorstraat 42 te Utrecht. Links een gedeelte van de Maartensbrug over de Oudegracht. Gemaakt tussen 1945 en 1955.'),
(11, 'en', 'Cellar on the Zoutmarkt', 'Restoration of the cellar on the Zoutmarkt behind Choorstraat 42 in Utrecht. On the left, part of the Maartensbrug over the Oudegracht. Taken between 1945 and 1955.'),
(12, 'nl', 'De Kalisbrug met het visafslaghuisje', 'Gezicht op de Oudegracht en de Vismarkt te Utrecht uit het zuiden, met de Kalisbrug met het visafslaghuisje en op de achtergrond de zijgevel van het stadhuis.'),
(12, 'en', 'The Kalisbrug with the fish auction house', 'View of the Oudegracht and the Vismarkt in Utrecht from the south, with the Kalisbrug and its fish auction house and the side of City Hall in the background.'),
(13, 'nl', 'Terras op de Kalisbrug', 'Het terras op de Kalisbrug te Utrecht, met op de achtergrond de panden aan de Vismarkt.'),
(13, 'en', 'Terrace on the Kalisbrug', 'The terrace on the Kalisbrug in Utrecht, with the buildings on the Vismarkt in the background.'),
(14, 'nl', 'De Kalisbrug en het stadhuis', 'Gezicht op de Kalisbrug te Utrecht, met op de voorgrond een terras op de werf langs de Oudegracht, geheel links de huizen aan de Zoutmarkt en rechts de Vismarkt. Op de achtergrond het Stadhuis.'),
(14, 'en', 'The Kalisbrug and City Hall', 'View of the Kalisbrug in Utrecht, with a terrace on the wharf along the Oudegracht in the foreground, the houses on the Zoutmarkt on the far left and the Vismarkt on the right. City Hall in the background.'),
(15, 'nl', 'Terras bij de Stadhuisbrug', 'Gezicht op het terras aan de Oudegracht te Utrecht ter hoogte van de Stadhuisbrug.'),
(15, 'en', 'Terrace at the Stadhuisbrug', 'View of the terrace on the Oudegracht in Utrecht near the Stadhuisbrug.'),
(16, 'nl', 'Uitzicht vanaf de Stadhuisbrug', 'Gezicht op de Oudegracht te Utrecht, vanaf de Stadhuisbrug, met het gebouw van de bank Vlaer en Kol (Oudegracht 158) en links de bioscopen Camera en Studio.'),
(16, 'en', 'View from the Stadhuisbrug', 'View of the Oudegracht in Utrecht from the Stadhuisbrug, with the building of the bank Vlaer en Kol (Oudegracht 158) and the Camera and Studio cinemas on the left.'),
(17, 'nl', 'De Stadhuisbrug', 'Gezicht op de westzijde van de Stadhuisbrug over de Oudegracht te Utrecht, uit het zuidwesten.'),
(17, 'en', 'The Stadhuisbrug', 'View of the west side of the Stadhuisbrug over the Oudegracht in Utrecht, from the south-west.');
