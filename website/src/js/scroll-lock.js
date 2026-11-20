// Pagina erachter vastzetten zolang er een venster open is (uitleg, pop-up
// van een locatie, vergrote kaart), zonder dat de pagina verspringt.
//
// Het probleem: overflow: hidden laat de scrollbalk verdwijnen. Dan wordt de
// pagina ±15px breder en schuift alles (ook de vaste header) een stukje op.
// De oplossing: precies die breedte opvullen met padding-right (zie de
// regels voor body.is-scroll-locked in main.css).
//
// Elk venster geeft een eigen naam mee ('help', 'modal', 'map-zoom'). Pas als
// het laatste venster dicht is, mag de pagina weer scrollen.

const owners = new Set()

export function lockScroll(owner) {
  if (owners.size === 0) {
    // Breedte van de scrollbalk = vensterbreedte min de breedte van de pagina
    const scrollbar = window.innerWidth - document.documentElement.clientWidth
    document.documentElement.style.setProperty('--scrollbar-width', `${scrollbar}px`)
    document.body.classList.add('is-scroll-locked')
  }
  owners.add(owner)
}

export function unlockScroll(owner) {
  owners.delete(owner)
  if (owners.size > 0) return
  document.body.classList.remove('is-scroll-locked')
  document.documentElement.style.removeProperty('--scrollbar-width')
}
