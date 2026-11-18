<?php
/**
 * Ronde ?-knop linksonder + "Zo werkt het"-venster met colofon.
 * Wordt alleen op de homepage geladen (zie index.php).
 * Openen/sluiten + genie-effect: src/js/help.js — opmaak: src/css/help.css
 *
 * Indeling (breed venster, zonder scrollen):
 *   ┌ titel + intro ─────────────────────────┐
 *   │ stappen + tip        │ colofon          │
 *   └ "vind me terug" ──────── [Aan de slag!] ┘
 *
 * Colofon-gegevens komen uit de catalogus van Het Utrechts Archief:
 *  - Film: archief 5801, fragment "Oudegracht" uit "Utrecht, zooals U het niet kent" (1929)
 *  - Kaart: beeldmateriaal catalogusnummer 217346 (1928)
 */
$ccDeed  = current_lang() === 'en' ? 'deed.en' : 'deed.nl';
$filmUrl = 'https://hetutrechtsarchief.nl/onderzoek/resultaten/archieven?mivast=39&mizig=210&miadt=39&miview=inv2&milang=nl&micode=5801&minr=38250006';
$mapUrl  = 'https://hetutrechtsarchief.nl/beeldmateriaal/detail/e829b91f-9834-a4e2-e053-8f04000ac603';
?>
<div class="help" id="help-dialog" data-help hidden>
    <div class="help__backdrop" data-help-close></div>

    <div class="help__panel" role="dialog" aria-modal="true" aria-labelledby="help-title" tabindex="-1" data-help-panel>
        <button class="help__close" type="button" data-help-close aria-label="<?= e(t('home.close')) ?>"></button>

        <div class="help__body" data-help-scroll>
            <header class="help__head">
                <h2 class="help__title" id="help-title"><?= e(t('help.title')) ?></h2>
                <p class="help__lead"><?= e(t('help.lead')) ?></p>
            </header>

            <!-- Links: uitleg in 4 stappen -->
            <div class="help__main">
                <ol class="help__steps">
                    <li class="help__step">
                        <div>
                            <h3><?= e(t('help.step1_title')) ?></h3>
                            <p><?= e(t('help.step1_text')) ?></p>
                        </div>
                    </li>
                    <li class="help__step">
                        <div>
                            <h3><?= e(t('help.step2_title')) ?></h3>
                            <p><?= e(t('help.step2_text')) ?></p>
                        </div>
                    </li>
                    <li class="help__step">
                        <div>
                            <div class="help__step-head">
                                <h3><?= e(t('help.step3_title')) ?></h3>
                                <!-- Voorbeeld van de echte knop, zodat kinderen weten waar ze op moeten letten -->
                                <span class="help__sample-hotspot" aria-hidden="true"><?= e(t('home.open_item')) ?></span>
                            </div>
                            <p><?= e(t('help.step3_text')) ?></p>
                        </div>
                    </li>
                    <li class="help__step">
                        <div>
                            <div class="help__step-head">
                                <h3><?= e(t('help.step4_title')) ?></h3>
                                <!-- Mini-tijdlijn: lichte knoppen + één donkere "huidige plek" -->
                                <span class="help__sample-timeline" aria-hidden="true"><span></span><span class="is-now"></span><span></span><span></span></span>
                            </div>
                            <p><?= e(t('help.step4_text')) ?></p>
                        </div>
                    </li>
                </ol>

                <p class="help__tip"><?= e(t('help.tip')) ?></p>
            </div>

            <!-- Rechts: colofon -->
            <section class="help__credits" aria-labelledby="help-credits-title">
                <h3 id="help-credits-title"><?= e(t('help.credits_title')) ?></h3>

                <div class="help__credit">
                    <h4><?= e(t('help.film_heading')) ?></h4>
                    <p class="help__credit-title"><cite>Utrecht, zooals U het niet kent</cite> (1929)</p>
                    <p><?= e(t('help.film_desc')) ?></p>
                    <p class="help__credit-meta">
                        Polygoon (<?= e(t('help.role_production')) ?>) · J. Jansen (<?= e(t('help.role_camera')) ?>) · J.H.C. van den Berg (<?= e(t('help.role_script')) ?>)
                    </p>
                    <p class="help__credit-meta">
                        Het Utrechts Archief, <?= e(t('help.archive_nr')) ?> ·
                        <a href="https://creativecommons.org/licenses/by/4.0/<?= $ccDeed ?>" target="_blank" rel="noopener">CC BY 4.0</a>
                    </p>
                    <a class="help__source" href="<?= e($filmUrl) ?>" target="_blank" rel="noopener"><?= e(t('help.view_source')) ?> ↗</a>
                </div>

                <div class="help__credit">
                    <h4><?= e(t('help.map_heading')) ?></h4>
                    <p class="help__credit-title"><cite>Plattegrond der Gemeente Utrecht</cite> (1928)</p>
                    <p><?= e(t('help.map_desc')) ?></p>
                    <p class="help__credit-meta"><?= e(t('help.maker_unknown')) ?> · <?= e(t('help.map_technique')) ?></p>
                    <p class="help__credit-meta">
                        Het Utrechts Archief, <?= e(t('help.catalog_nr')) ?> ·
                        <a href="https://creativecommons.org/publicdomain/mark/1.0/<?= $ccDeed ?>" target="_blank" rel="noopener"><?= e(t('help.public_domain')) ?></a>
                    </p>
                    <a class="help__source" href="<?= e($mapUrl) ?>" target="_blank" rel="noopener"><?= e(t('help.view_source')) ?> ↗</a>
                </div>
            </section>
        </div>

        <div class="help__footer">
            <p class="help__remember"><span class="help__remember-icon" aria-hidden="true">?</span><?= e(t('help.remember')) ?></p>
            <button class="help__start" type="button" data-help-close><?= e(t('help.start')) ?></button>
        </div>
    </div>
</div>

<!-- position: fixed in help.css → blijft linksonder in beeld, hoe ver je ook scrolt -->
<button class="help-fab" type="button" data-help-toggle aria-controls="help-dialog" aria-expanded="false"
        aria-label="<?= e(t('help.fab_label')) ?>" title="<?= e(t('help.fab_label')) ?>">
    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.6"
         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M9.1 9a3 3 0 1 1 4.4 2.65c-.9.5-1.5 1.2-1.5 2.35v.5"/>
        <circle cx="12" cy="18" r="0.6" fill="currentColor"/>
    </svg>
</button>
