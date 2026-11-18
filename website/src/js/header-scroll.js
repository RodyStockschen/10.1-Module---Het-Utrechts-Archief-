// Zelfde gedrag als hetutrechtsarchief.nl: zodra je meer dan 10px naar
// beneden scrolt krijgt <body> de class "is-scrolled". De CSS (header.css)
// laat het logo dan krimpen en wisselt het volledige logo voor het compacte.
// Hier zit dus géén animatie-code: JS zet alleen een class aan/uit.
export function initHeaderScroll() {
  const update = () => {
    document.body.classList.toggle('is-scrolled', window.scrollY > 10)
  }

  // passive: true = de browser hoeft niet te wachten op deze functie,
  // waardoor scrollen soepel blijft.
  window.addEventListener('scroll', update, { passive: true })
  update() // direct checken: de pagina kan al gescrold geladen worden (refresh)
}
