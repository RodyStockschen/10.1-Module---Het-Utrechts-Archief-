<?php
require_once __DIR__ . '/includes/bootstrap.php';

$lang  = current_lang();
$focus = settings();

$locations = q(
    "SELECT l.id, l.slug, l.video_start_sec, l.video_end_sec, l.map_x_pct, l.map_y_pct,
            i.name, i.short_description, i.archive_text
     FROM locations l
     JOIN location_i18n i ON i.location_id = l.id AND i.lang = ?
     ORDER BY l.sort_order, l.video_start_sec",
    [$lang]
)->fetchAll();

$page_title = t('site.title') . ' – Het Utrechts Archief';
$page_entry = 'src/js/main.js';
$body_class = 'page-home';

require __DIR__ . '/includes/header.php';
?>

<section class="hero-banner">
    <div class="hero-banner__bar">
        <h1 class="hero-banner__title"><?= e(t('home.intro_title')) ?></h1>
    </div>
</section>

<section id="player" class="player"
         data-player
         data-video-duration="183"
         data-locations='<?= e(json_encode(array_map(fn($l) => [
             'id'    => (int)$l['id'],
             'slug'  => $l['slug'],
             'name'  => $l['name'],
             'short'   => $l['short_description'],          // kindertekst
             'archive' => (string)($l['archive_text'] ?? ''), // tekst van het archief
             'start' => (float)$l['video_start_sec'],
             'end'   => (float)$l['video_end_sec'],
             'x'     => (float)$l['map_x_pct'],
             'y'     => (float)$l['map_y_pct'],
         ], $locations), JSON_UNESCAPED_UNICODE)) ?>'
         data-close-label="<?= e(t('home.close')) ?>"
         data-archive-heading="<?= e(t('home.archive_heading')) ?>"
         data-year-label="<?= e(t('home.year_label')) ?>"
         data-source-label="<?= e(t('help.view_source')) ?>"
         data-lang="<?= e($lang) ?>">
    <div class="player__stage">
        <div class="player__video-wrap">
            <!-- controlsList="nofullscreen": verbergt de volledig-scherm-knop van de
                 browser (Chrome/Edge). Die maakt alleen de video groot, waardoor de
                 pulserende stip en de pop-up wegvallen. We gebruiken onze eigen knop. -->
            <video class="player__video"
                   data-video
                   controls
                   controlsList="nofullscreen"
                   preload="metadata"
                   poster=""
                   playsinline>
                <source src="/assets/video/hua-oudegracht-1929-mail.mp4" type="video/mp4">
            </video>

            <!-- Rood locatielabel met pulserende stip en meteen de naam van de plek.
                 Eén klik = pop-up met archiefstukken (player.js) -->
            <div class="player__hotspot" data-hotspot hidden>
                <button class="player__hotspot-btn" type="button" data-hotspot-btn aria-haspopup="dialog">
                    <span class="player__hotspot-dot" aria-hidden="true"></span>
                    <span class="player__hotspot-label" data-hotspot-label></span>
                </button>
            </div>

            <!-- Knop linksboven: de info (rode labels) in de video verbergen/tonen,
                 zodat je de film ook zonder labels kunt kijken (player.js) -->
            <button class="player__icon-btn player__waypoints-toggle" type="button" data-waypoints-toggle
                    aria-label="<?= e(t('home.waypoints_hide')) ?>" title="<?= e(t('home.waypoints_hide')) ?>"
                    data-label-hide="<?= e(t('home.waypoints_hide')) ?>"
                    data-label-show="<?= e(t('home.waypoints_show')) ?>">
                <!-- Rond icoon (rondje met stip, zoals de oude ronde knop): info staat aan -->
                <svg class="player__waypoints-on" viewBox="0 0 24 24" width="22" height="22" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="8"/>
                    <circle cx="12" cy="12" r="3" fill="currentColor" stroke="none"/>
                </svg>
                <!-- Hetzelfde rondje, doorgestreept: info staat uit -->
                <svg class="player__waypoints-off" viewBox="0 0 24 24" width="22" height="22" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="8"/>
                    <circle cx="12" cy="12" r="3" fill="currentColor" stroke="none"/>
                    <path d="M4.5 4.5l15 15"/>
                </svg>
            </button>

            <!-- Eigen knop voor volledig scherm: maakt de hele omlijsting groot
                 (video + stip + pop-up). Zie video-fullscreen.js -->
            <button class="player__icon-btn player__fullscreen" type="button" data-fullscreen-toggle
                    aria-label="<?= e(t('home.fullscreen')) ?>" title="<?= e(t('home.fullscreen')) ?>"
                    data-label-enter="<?= e(t('home.fullscreen')) ?>"
                    data-label-exit="<?= e(t('home.fullscreen_exit')) ?>">
                <svg class="player__fullscreen-enter" viewBox="0 0 24 24" width="22" height="22" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>
                </svg>
                <svg class="player__fullscreen-exit" viewBox="0 0 24 24" width="22" height="22" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"/>
                </svg>
            </button>
        </div>

        <aside class="minimap" aria-label="<?= e(t('home.map_label')) ?>">
            <div class="minimap__header">
                <h2 class="minimap__title"><?= e(t('home.map_label')) ?></h2>
                <button class="minimap__zoom-btn"
                        type="button"
                        data-map-zoom
                        aria-label="<?= e(t('home.zoom_map')) ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 3h6M3 3v6M21 3h-6M21 3v6M3 21h6M3 21v-6M21 21h-6M21 21v-6"/>
                    </svg>
                    <span><?= e(t('home.zoom_map')) ?></span>
                </button>
            </div>
            <div class="minimap__frame"
                 data-minimap
                 data-focus-x="<?= (float)$focus['minimap_focus_x'] ?>"
                 data-focus-y="<?= (float)$focus['minimap_focus_y'] ?>"
                 data-focus-zoom="<?= (float)$focus['minimap_focus_zoom'] ?>">
                <div class="minimap__stage" data-minimap-stage>
                    <img class="minimap__image"
                         data-minimap-image
                         src="/assets/img/plattegrond-utrecht-1929-full.jpg?v=2"
                         alt="Plattegrond van Utrecht 1929">

                    <?php
                    $routePoints = implode(' ', array_map(
                        fn($l) => sprintf('%.2f,%.2f', (float)$l['map_x_pct'], (float)$l['map_y_pct']),
                        $locations
                    ));
                    ?>
                    <svg class="route-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                        <polyline class="route-svg__full" points="<?= e($routePoints) ?>" vector-effect="non-scaling-stroke" />
                        <polyline class="route-svg__progress" data-route-progress points="" vector-effect="non-scaling-stroke" />
                    </svg>

                    <?php foreach ($locations as $loc): ?>
                        <button class="minimap__pin"
                                type="button"
                                data-pin="<?= (int)$loc['id'] ?>"
                                data-start="<?= (float)$loc['video_start_sec'] ?>"
                                style="left: <?= (float)$loc['map_x_pct'] ?>%; top: <?= (float)$loc['map_y_pct'] ?>%;"
                                aria-label="<?= e($loc['name']) ?>">
                            <span class="minimap__pin-dot" aria-hidden="true"></span>
                        </button>
                    <?php endforeach; ?>
                    <div class="minimap__marker" data-marker aria-hidden="true">
                        <span class="minimap__marker-dot"></span>
                        <span class="minimap__marker-pulse"></span>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Geen zichtbare kop; role="group" + aria-label laat een schermlezer
         de naam van de tijdlijn wel voorlezen. -->
    <div class="timeline" role="group" aria-label="<?= e(t('home.timeline_label')) ?>">
        <!-- Lichtrode balk = momenten zonder locatie (klik = naar dat moment).
             Rode blokken = locaties; donkerrood = waar de film nu is. -->
        <div class="timeline__track" data-timeline>
            <?php foreach ($locations as $loc):
                $leftPct  = (float)$loc['video_start_sec'] / 183 * 100;
                $widthPct = ((float)$loc['video_end_sec'] - (float)$loc['video_start_sec']) / 183 * 100;
                $startLabel = format_time((float)$loc['video_start_sec']); // bijv. 105 → "1:45"
            ?>
                <button class="timeline__segment"
                        type="button"
                        data-segment="<?= (int)$loc['id'] ?>"
                        data-start="<?= (float)$loc['video_start_sec'] ?>"
                        style="left: <?= $leftPct ?>%; width: <?= $widthPct ?>%;"
                        title="<?= e($loc['name']) ?>">
                    <span class="timeline__segment-time"><?= e($startLabel) ?></span>
                    <span class="timeline__segment-label"><?= e($loc['name']) ?></span>
                </button>
            <?php endforeach; ?>
            <!-- Verticaal lijntje: waar de film nu is (player.js zet de positie) -->
            <div class="timeline__playhead" data-timeline-playhead aria-hidden="true"></div>
        </div>
    </div>
</section>

<div class="modal" data-modal hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal__backdrop" data-modal-close></div>
    <div class="modal__panel">
        <!-- De sluitknop staat in een 'plakkende' balk (position: sticky, zie
             modal.css): de X blijft rechtsboven, ook als je in de pop-up scrolt. -->
        <div class="modal__close-bar">
            <button class="modal__close" type="button" data-modal-close aria-label="<?= e(t('home.close')) ?>">✕</button>
        </div>
        <h3 class="modal__title" id="modal-title" data-modal-title></h3>
        <!-- Eerst de korte kindertekst, daaronder de originele tekst van het archief.
             Lege teksten worden verborgen (modal.js), bijv. bij plekken met alleen foto's. -->
        <p class="modal__short" data-modal-short></p>
        <section class="modal__archive" data-modal-archive hidden>
            <h4 class="modal__archive-title"><?= e(t('home.archive_text_heading')) ?></h4>
            <p class="modal__archive-text" data-modal-archive-text></p>
        </section>
        <div class="modal__items" data-modal-items></div>
    </div>
</div>

<div class="map-zoom" data-map-zoom-modal hidden aria-hidden="true" role="dialog" aria-modal="true">
    <div class="map-zoom__backdrop" data-map-zoom-close></div>
    <div class="map-zoom__stage">
        <div class="map-zoom__toolbar">
            <button type="button" class="map-zoom__btn map-zoom__btn--wide" data-map-zoom-toggle-pins aria-pressed="true">
                <span data-toggle-text-on><?= e(t('home.hide_pins')) ?></span>
                <span data-toggle-text-off hidden><?= e(t('home.show_pins')) ?></span>
            </button>
            <!-- Zoombalk (i.p.v. + en −): links = hele kaart, rechts = maximaal
                 ingezoomd. Schuift vanzelf mee als je met scrollen zoomt (map-zoom.js).
                 Een <input type="range"> werkt ook met het toetsenbord (pijltjes). -->
            <label class="map-zoom__slider">
                <svg class="map-zoom__slider-icon" viewBox="0 0 24 24" width="18" height="18" fill="none"
                     stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                    <circle cx="10.5" cy="10.5" r="6.5"/>
                    <path d="M15.5 15.5L21 21"/>
                </svg>
                <input type="range" min="0" max="100" step="1" value="0"
                       data-map-zoom-slider aria-label="<?= e(t('home.zoom_slider')) ?>">
            </label>
            <button type="button" class="map-zoom__btn" data-map-zoom-reset aria-label="Reset">↺</button>
            <button type="button" class="map-zoom__btn map-zoom__btn--close" data-map-zoom-close aria-label="<?= e(t('home.close')) ?>">✕</button>
        </div>
        <div class="map-zoom__viewport" data-map-zoom-viewport>
            <div class="map-zoom__stage" data-map-zoom-stage>
                <img class="map-zoom__image"
                     data-map-zoom-image
                     src="/assets/img/plattegrond-utrecht-1929-full.jpg?v=2"
                     alt="Plattegrond van Utrecht 1929 – volledige weergave"
                     draggable="false">

                <svg class="route-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <polyline class="route-svg__full" points="<?= e($routePoints) ?>" vector-effect="non-scaling-stroke" />
                    <polyline class="route-svg__progress" data-route-progress points="" vector-effect="non-scaling-stroke" />
                </svg>

                <?php foreach ($locations as $loc): ?>
                    <!-- Klik = pop-up van deze locatie, met genie uit de stip (player.js) -->
                    <button class="map-zoom__pin"
                            type="button"
                            data-location-id="<?= (int)$loc['id'] ?>"
                            data-start="<?= (float)$loc['video_start_sec'] ?>"
                            style="left: <?= (float)$loc['map_x_pct'] ?>%; top: <?= (float)$loc['map_y_pct'] ?>%;"
                            aria-label="<?= e($loc['name']) ?>"
                            aria-haspopup="dialog">
                        <span class="map-zoom__pin-dot" aria-hidden="true"></span>
                        <span class="map-zoom__pin-label"><?= e($loc['name']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/help.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
