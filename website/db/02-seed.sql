SET NAMES utf8mb4;

-- Singleton settings-rij (minimap focus-gebied)
INSERT INTO settings (id) VALUES (1);

-- Admin user: admin@hua.local / admin (verander dit na installatie!)
INSERT INTO admins (email, password_hash) VALUES
('admin@hua.local', '$2y$12$PVDrEHVAh8/LoTdEUk3j6OF9EvkQIecTt9CN4zP8LiC9XV7J8fiMu');

-- 5 historische locaties langs de Oudegracht, verspreid over de 182s video
INSERT INTO locations (id, slug, video_start_sec, video_end_sec, map_x_pct, map_y_pct, sort_order) VALUES
(1, 'domtoren',         0,   30,  52.00, 38.00, 1),
(2, 'stadhuisbrug',    30,   60,  48.00, 44.00, 2),
(3, 'bakkerbrug',      60,  105,  46.00, 52.00, 3),
(4, 'winkel-van-sinkel',105, 145, 50.00, 46.00, 4),
(5, 'geertebrug',     145,  183,  44.00, 62.00, 5);

INSERT INTO location_i18n (location_id, lang, name, short_description) VALUES
(1, 'nl', 'Domtoren',
   'De Domtoren is de hoogste kerktoren van Nederland en hoort bij Utrecht als geen ander gebouw. Hij is in 1382 klaar en je kunt hem vanaf de hele Oudegracht zien.'),
(1, 'en', 'Dom Tower',
   'The Dom Tower is the tallest church tower in the Netherlands and belongs to Utrecht like no other building. It was finished in 1382 and you can spot it from all along the Oudegracht canal.'),

(2, 'nl', 'Stadhuisbrug',
   'De Stadhuisbrug is al honderden jaren het drukste plein bij de Oudegracht. Hier stond vroeger de vismarkt en nu zie je er het stadhuis van Utrecht.'),
(2, 'en', 'City Hall Bridge',
   'The City Hall Bridge has been the busiest square at the Oudegracht for centuries. In the old days this was the fish market; today you see the Utrecht city hall here.'),

(3, 'nl', 'Bakkerbrug',
   'De Bakkerbrug is al bijna 700 jaar oud. De brug is vernoemd naar de bakkers die hier vroeger hun brood verkochten.'),
(3, 'en', 'Baker\'s Bridge',
   'The Baker\'s Bridge is almost 700 years old. It is named after the bakers who used to sell their bread right here.'),

(4, 'nl', 'Winkel van Sinkel',
   'De Winkel van Sinkel was in 1839 het eerste warenhuis van Nederland. Je kon er álles kopen: van kleding en speelgoed tot horloges. Nu is het een café en restaurant.'),
(4, 'en', 'Winkel van Sinkel',
   'The Winkel van Sinkel opened in 1839 as the first department store in the Netherlands. You could buy almost anything there: from clothes and toys to watches. Today it is a café and restaurant.'),

(5, 'nl', 'Geertebrug',
   'De Geertebrug staat bij de Geertekerk uit de 13e eeuw. Vanaf deze brug zie je het mooie zuidelijke deel van de Oudegracht.'),
(5, 'en', 'Geerte Bridge',
   'The Geerte Bridge sits next to the 13th century Geertekerk church. From this bridge you get a beautiful view of the southern stretch of the Oudegracht.');

-- Eén placeholder archiefstuk per locatie — in productie uploadt beheerder meer.
INSERT INTO archive_items (id, location_id, image_path, year, sort_order) VALUES
(1, 1, 'assets/img/oudegracht-plattegrond.jpg', 1929, 1),
(2, 2, 'assets/img/oudegracht-plattegrond.jpg', 1929, 1),
(3, 3, 'assets/img/oudegracht-plattegrond.jpg', 1929, 1),
(4, 4, 'assets/img/oudegracht-plattegrond.jpg', 1929, 1),
(5, 5, 'assets/img/oudegracht-plattegrond.jpg', 1929, 1);

INSERT INTO archive_i18n (archive_item_id, lang, title, description) VALUES
(1, 'nl', 'Plattegrond van Utrecht (circa 1929)', 'Een plattegrond uit de tijd van de video. Rond de Domtoren zie je de smalle straatjes van het middeleeuwse Utrecht.'),
(1, 'en', 'Map of Utrecht (c. 1929)', 'A city plan from the same era as the film. Around the Dom Tower you can see the narrow medieval streets.'),
(2, 'nl', 'De Stadhuisbrug rond 1929', 'Op de plattegrond zie je hoe druk de Stadhuisbrug toen was: het middelpunt van de stad.'),
(2, 'en', 'The City Hall Bridge around 1929', 'The map shows how busy the City Hall Bridge was back then: the very centre of the city.'),
(3, 'nl', 'De Bakkerbrug op de kaart', 'De Bakkerbrug ligt midden op de Oudegracht en is altijd een belangrijke oversteek geweest.'),
(3, 'en', 'The Baker\'s Bridge on the map', 'The Baker\'s Bridge sits in the middle of the Oudegracht and has always been an important crossing.'),
(4, 'nl', 'Winkel van Sinkel op de plattegrond', 'Het gebouw van de Winkel van Sinkel stond al op kaarten uit 1839 en is nog steeds te zien.'),
(4, 'en', 'Winkel van Sinkel on the city plan', 'The Winkel van Sinkel building appeared on city plans as early as 1839 and still stands today.'),
(5, 'nl', 'De Geertebrug op de kaart', 'Het zuidelijke deel van de Oudegracht met de Geertebrug en de Geertekerk eromheen.'),
(5, 'en', 'The Geerte Bridge on the map', 'The southern stretch of the Oudegracht with the Geerte Bridge and the Geertekerk church nearby.');
