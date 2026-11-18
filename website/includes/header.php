<?php
/** @var string $page_title */
$page_title  = $page_title  ?? t('site.title');
$page_entry  = $page_entry  ?? 'src/js/main.js';
$body_class  = $body_class  ?? 'page-home';
$menu = [
    'onderzoek' => [
        ['https://hetutrechtsarchief.nl/onderzoek/collecties?view=collections', 'Collecties'],
        ['https://hetutrechtsarchief.nl/onderzoek/zoek-op-onderwerp', 'Zoek op onderwerp'],
        ['https://hetutrechtsarchief.nl/onderzoek/studiezaal', 'Studiezaal'],
        ['https://hetutrechtsarchief.nl/onderzoek/bouwdossiers', 'Bouwdossiers'],
        ['https://hetutrechtsarchief.nl/onderzoek/archiefstuk-reserveren', 'Archiefstuk inzien'],
        ['https://hetutrechtsarchief.nl/onderzoek/verzoek-tot-digitaliseren', 'Verzoek tot digitaliseren'],
        ['https://hetutrechtsarchief.nl/onderzoek/help-mee', 'Helpt u mee?'],
        ['https://hetutrechtsarchief.nl/onderzoek/open-data', 'Open data'],
        ['https://hetutrechtsarchief.nl/onderzoek/openbaarheid-auteursrecht', 'Openbaarheid'],
    ],
    'ontdekken' => [
        ['https://hetutrechtsarchief.nl/ontdekken/tentoonstellingen', 'Tentoonstellingen'],
        ['https://hetutrechtsarchief.nl/ontdekken/agenda', 'Activiteiten'],
        ['https://hetutrechtsarchief.nl/ontdekken/families-en-kinderen', 'Families en kinderen'],
        ['https://hetutrechtsarchief.nl/ontdekken/plan-je-bezoek', 'Plan je bezoek'],
        ['https://hetutrechtsarchief.nl/ontdekken/rondleidingen', 'Rondleidingen'],
        ['https://hetutrechtsarchief.nl/ontdekken/verhalen', 'Verhalen'],
        ['https://hetutrechtsarchief.nl/ontdekken/podcasts', 'Podcast'],
        ['https://hetutrechtsarchief.nl/ontdekken/utrecht-time-machine', 'Utrecht Time Machine'],
    ],
    'onderwijs' => [
        ['https://hetutrechtsarchief.nl/onderwijs/basis-onderwijs', 'Primair onderwijs'],
        ['https://hetutrechtsarchief.nl/onderwijs/voortgezet-onderwijs', 'Voortgezet onderwijs'],
        ['https://hetutrechtsarchief.nl/onderwijs/taalklassen-aanbod', 'Taalklassen aanbod'],
        ['https://hetutrechtsarchief.nl/onderwijs/studenten', 'Studenten'],
        ['https://hetutrechtsarchief.nl/onderwijs/cursussen', 'Cursussen'],
        ['https://hetutrechtsarchief.nl/onderwijs/voorwaarden-groepsbezoek', 'Voorwaarden Groepsbezoek'],
    ],
    'vakgenoten' => [
        ['https://hetutrechtsarchief.nl/vakgenoten/e-depot', 'e-depot'],
        ['https://hetutrechtsarchief.nl/vakgenoten/archiefbeheer', 'Archiefbeheer'],
        ['https://hetutrechtsarchief.nl/vakgenoten/toezicht', 'Toezicht'],
        ['https://hetutrechtsarchief.nl/vakgenoten/toezicht-in-de-praktijk', 'Toezicht in de praktijk'],
    ],
    'overons' => [
        ['https://hetutrechtsarchief.nl/over-ons/archief-overdragen', 'Archief overdragen'],
        ['https://hetutrechtsarchief.nl/over-ons/missie', 'Beleid'],
        ['https://hetutrechtsarchief.nl/over-ons/projecten', 'Projecten'],
        ['https://hetutrechtsarchief.nl/over-ons/nieuws', 'Nieuws'],
        ['https://hetutrechtsarchief.nl/over-ons/medewerkers', 'Medewerkers'],
        ['https://hetutrechtsarchief.nl/over-ons/vacatures', 'Vacatures'],
        ['https://hetutrechtsarchief.nl/over-ons/word-vriend', 'Word vriend'],
        ['https://hetutrechtsarchief.nl/over-ons/toegankelijkheid', 'Toegankelijkheid'],
        ['https://hetutrechtsarchief.nl/over-ons/heeft-u-een-klacht', 'Heeft u een klacht?'],
    ],
];
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e(t('site.tagline')) ?>">
<link rel="icon" href="/assets/img/Logo_ingeklapt.png" type="image/png">
<?= vite_assets($page_entry) ?>
</head>
<body class="<?= e($body_class) ?>">
<header class="site-header" data-dropdowns>
    <div class="header-inner">
        <button class="search-toggle" type="button" aria-controls="site-search" aria-expanded="false" data-search-toggle>
            <span class="search_icon" aria-hidden="true">
                <img src="/assets/img/search_icon.png" alt="<?= e(t('header.searchIconAlt')) ?>">
            </span>
            <span class="search_close" hidden>✕</span>
            <span class="search_openen_"><?= e(t('header.searchOpenen')) ?></span>
        </button>

        <nav class="main-nav" aria-label="<?= e(t('header.menu.onderzoek')) ?>">
            <ul class="main-nav__list">
                <?php foreach ($menu as $key => $items): ?>
                    <li class="<?= e($key) ?>_nav" data-dropdown="<?= e($key) ?>">
                        <button class="main-nav__link" type="button" aria-expanded="false" data-dropdown-toggle>
                            <span class="dropdown_arrow">›</span>
                            <span><?= e(t("header.menu.$key")) ?></span>
                        </button>
                        <ul class="dropdown-menu">
                            <?php foreach ($items as [$href, $label]): ?>
                                <li><a href="<?= e($href) ?>"><?= e($label) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>

                <li class="contact_nav">
                    <a href="https://hetutrechtsarchief.nl/contact" class="main-nav__link main-nav__link--plain"><?= e(t('header.contact')) ?></a>
                </li>
                <li class="english_nav">
                    <a href="<?= e(lang_url(other_lang())) ?>" class="main-nav__link main-nav__link--italic"><?= e(t('header.langToggle')) ?></a>
                </li>
            </ul>
        </nav>

        <!-- Twee logo's, net als hetutrechtsarchief.nl: bovenaan de pagina het
             volledige logo, na scrollen het compacte (zie header-scroll.js). -->
        <a href="/" class="site-logo">
            <img class="site-logo__full" src="/assets/img/Logo_uitgeklapt.png" alt="Het Utrechts Archief">
            <img class="site-logo__compact" src="/assets/img/Logo_ingeklapt.png" alt="" aria-hidden="true">
        </a>
    </div>

    <div class="search-bar" id="site-search" hidden>
        <form action="https://hetutrechtsarchief.nl/zoeken" method="get" class="search-bar__form">
            <input type="search" name="q" placeholder="<?= e(t('header.searchPlaceholder')) ?>">
            <button type="submit"><?= e(t('header.searchSubmit')) ?></button>
        </form>
    </div>
</header>

<main class="site-main">
