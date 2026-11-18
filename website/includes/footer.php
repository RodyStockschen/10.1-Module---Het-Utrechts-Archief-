</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-columns">
            <div class="footer-column">
                <h3 class="footer-title"><?= e(t('footer.plan_title')) ?></h3>
                <ul class="footer-linklist">
                    <li><a href="https://hetutrechtsarchief.nl/expo">Expo – Hamburgerstraat 28</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/studiezaal-en-depot">Studiezaal – Alexander Numankade 199–201</a></li>
                </ul>

                <h4 class="footer-subtitle"><?= e(t('footer.research_title')) ?></h4>
                <ul class="footer-linklist">
                    <li><a href="https://hetutrechtsarchief.nl/onderzoek/collecties">Archieven doorzoeken</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/beeldmateriaal-bekijken/">Beeldmateriaal bekijken</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/bouwtekeningen-en-woonomgeving">Bouwtekeningen</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/onderzoek/resultaten/personen-mais">Personen zoeken</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3 class="footer-title"><?= e(t('footer.about_title')) ?></h3>
                <ul class="footer-linklist">
                    <li><a href="https://hetutrechtsarchief.nl/over-ons/nieuws">Nieuws</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/ontdekken/agenda">Agenda</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/uw-materiaal-in-ons-archief">Uw materiaal in ons archief</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/contact">Contact</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/toegankelijkheid">Toegankelijkheid</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/disclaimer-colofon">Auteursrecht en disclaimer</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/privacyverklaring">Privacyverklaring</a></li>
                    <li><a href="https://hetutrechtsarchief.nl/anbi">ANBI</a></li>
                    <li><a href="<?= e(lang_url(other_lang())) ?>" class="footer-link-italic"><?= e(t('header.langToggle')) ?></a></li>
                </ul>
            </div>

            <div class="footer-column-contact">
                <h3 class="footer-title"><?= e(t('footer.contact_title')) ?></h3>
                <ul class="footer-contact-list">
                    <li><span class="footer-contact-icon">📞</span><span>(030) 286 66 11</span></li>
                    <li><span class="footer-contact-icon">@</span><a href="mailto:inlichtingen@hetutrechtsarchief.nl">inlichtingen@hetutrechtsarchief.nl</a></li>
                    <li><span class="footer-contact-icon">✉️</span><span>Postbus 131, 3500 AC Utrecht</span></li>
                    <li><span class="footer-contact-icon">💬</span><span>Chat: di t/m do 9.00 – 13.00 uur</span></li>
                </ul>
                <div class="footer-social">
                    <p class="footer-subtitle"><?= e(t('footer.follow')) ?></p>
                    <div class="footer-social-links">
                        <a href="https://www.facebook.com/HetUtrechtsArchief" aria-label="Facebook">F</a>
                        <a href="https://www.instagram.com/hetutrechtsarchief/" aria-label="Instagram">I</a>
                        <a href="https://www.youtube.com/user/hetutrechtsarchief" aria-label="YouTube">Y</a>
                        <a href="https://hetutrechtsarchief.nl/over-ons/nieuws?format=feed" aria-label="RSS">RSS</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-newsletter">
            <h3 class="footer-newsletter-title"><?= e(t('footer.newsletter_title')) ?></h3>
            <form action="#" method="post" class="footer-newsletter-form" onsubmit="event.preventDefault()">
                <input type="email" name="email" placeholder="<?= e(t('footer.email_placeholder')) ?>">
                <button type="submit"><?= e(t('footer.submit')) ?></button>
            </form>
        </div>

        <div class="footer-kvk">
            <p>IBAN: NL66RABO0123881641</p>
            <p>KvK: 62047302</p>
            <p>BTW: NL807024594B01</p>
        </div>
    </div>
</footer>
</body>
</html>
