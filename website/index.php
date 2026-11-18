<?php
require_once __DIR__ . '/includes/bootstrap.php';

$lang  = current_lang();
$focus = settings();

$locations = q(
    "SELECT l.id, l.slug, l.video_start_sec, l.video_end_sec, l.map_x_pct, l.map_y_pct,
            i.name, i.short_description
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
             'short' => $l['short_description'],
             'start' => (float)$l['video_start_sec'],
             'end'   => (float)$l['video_end_sec'],
             'x'     => (float)$l['map_x_pct'],
             'y'     => (float)$l['map_y_pct'],
         ], $locations), JSON_UNESCAPED_UNICODE)) ?>'
         data-open-label="<?= e(t('home.open_item')) ?>"
         data-close-label="<?= e(t('home.close')) ?>"
         data-archive-heading="<?= e(t('home.archive_heading')) ?>"
         data-year-label="<?= e(t('home.year_label')) ?>"
         data-lang="<?= e($lang) ?>">
    <div class="player__stage">
        <div class="player__video-wrap">
            <video class="player__video"
                   data-video
                   controls
                   preload="metadata"
                   poster=""
                   playsinline>
                <source src="/assets/video/hua-oudegracht-1929-mail.mp4" type="video/mp4">
            </video>
            <div class="player__hotspot" data-hotspot hidden>
                <button class="player__hotspot-btn" type="button" data-hotspot-btn>
                    <span class="player__hotspot-dot" aria-hidden="true"></span>
                    <span class="player__hotspot-label" data-hotspot-label></span>
                </button>
            </div>
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
                         src="/assets/img/plattegrond-utrecht-1929-full.jpg"
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

    <div class="timeline" aria-label="<?= e(t('home.timeline_label')) ?>">
        <h2 class="timeline__title"><?= e(t('home.timeline_label')) ?></h2>
        <div class="timeline__track" data-timeline>
            <div class="timeline__progress" data-timeline-progress></div>
            <?php foreach ($locations as $loc):
                $leftPct  = (float)$loc['video_start_sec'] / 183 * 100;
                $widthPct = ((float)$loc['video_end_sec'] - (float)$loc['video_start_sec']) / 183 * 100;
                // Starttijd als m:ss, bijv. 105 seconden → "1:45"
                $startSec   = (int) round((float)$loc['video_start_sec']);
                $startLabel = sprintf('%d:%02d', intdiv($startSec, 60), $startSec % 60);
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
        </div>
    </div>
</section>

<div class="modal" data-modal hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal__backdrop" data-modal-close></div>
    <div class="modal__panel">
        <button class="modal__close" type="button" data-modal-close aria-label="<?= e(t('home.close')) ?>">✕</button>
        <h3 class="modal__title" id="modal-title" data-modal-title></h3>
        <p class="modal__short" data-modal-short></p>
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
            <button type="button" class="map-zoom__btn" data-map-zoom-in aria-label="+">+</button>
            <button type="button" class="map-zoom__btn" data-map-zoom-out aria-label="−">−</button>
            <button type="button" class="map-zoom__btn" data-map-zoom-reset aria-label="Reset">↺</button>
            <button type="button" class="map-zoom__btn map-zoom__btn--close" data-map-zoom-close aria-label="<?= e(t('home.close')) ?>">✕</button>
        </div>
        <div class="map-zoom__viewport" data-map-zoom-viewport>
            <div class="map-zoom__stage" data-map-zoom-stage>
                <img class="map-zoom__image"
                     data-map-zoom-image
                     src="/assets/img/plattegrond-utrecht-1929-full.jpg"
                     alt="Plattegrond van Utrecht 1929 – volledige weergave"
                     draggable="false">

                <svg class="route-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <polyline class="route-svg__full" points="<?= e($routePoints) ?>" vector-effect="non-scaling-stroke" />
                    <polyline class="route-svg__progress" data-route-progress points="" vector-effect="non-scaling-stroke" />
                </svg>

                <?php foreach ($locations as $loc): ?>
                    <button class="map-zoom__pin"
                            type="button"
                            data-start="<?= (float)$loc['video_start_sec'] ?>"
                            style="left: <?= (float)$loc['map_x_pct'] ?>%; top: <?= (float)$loc['map_y_pct'] ?>%;"
                            aria-label="<?= e($loc['name']) ?>">
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
