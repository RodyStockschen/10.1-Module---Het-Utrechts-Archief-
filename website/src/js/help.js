// "Zo werkt het"-venster met colofon + hint (HTML: includes/help.php, opmaak: help.css)
//
// - Opent NIET vanzelf. Bij het openen van de site verschijnt een kleine hint
//   naast de ?-knop, die na ±20 seconden weer verdwijnt.
// - Openen via de ?-knop (of de hint): het venster komt met een genie-effect
//   uit de knop tevoorschijn (zoals een venster op de Mac uit het Dock).
// - Sluiten: dezelfde animatie achteruit, het venster wordt de knop in gezogen.
// - Knop "Colofon" wisselt het venster naar de colofon, "Terug" weer terug.
// Het genie-effect zelf staat in genie.js (wordt ook door de pop-up gebruikt).

import { genie, prefersReducedMotion } from './genie.js'
import { lockScroll, unlockScroll } from './scroll-lock.js'

const HINT_DELAY_MS = 800      // even wachten tot de pagina staat
const HINT_DURATION_MS = 20000 // na ±20 seconden verdwijnt de hint vanzelf

export function initHelp() {
  const fab = document.querySelector('[data-help-toggle]')
  const dialog = document.querySelector('[data-help]')
  if (!fab || !dialog) return

  const panel = dialog.querySelector('[data-help-panel]')
  const views = dialog.querySelectorAll('[data-help-view]')
  const switches = dialog.querySelectorAll('[data-help-show]')
  const hint = document.querySelector('[data-help-hint]')
  let isOpen = false
  let busy = false // voorkomt dubbelklikken tijdens een animatie
  let hintTimer = null

  function setOpenState(open) {
    isOpen = open
    fab.setAttribute('aria-expanded', String(open))
    fab.classList.toggle('is-open', open)
    // Pagina erachter niet laten scrollen, zonder dat hij verspringt
    if (open) lockScroll('help')
    else unlockScroll('help')
  }

  // ---------- Scherm wisselen: "guide" (uitleg) of "credits" (colofon) -------
  function showView(name, { focus = false } = {}) {
    views.forEach((view) => { view.hidden = view.dataset.helpView !== name })
    // De wisselknop voor het scherm waar je al bent, verbergen
    switches.forEach((btn) => { btn.hidden = btn.dataset.helpShow === name })

    // Het venster heet zoals de titel van het zichtbare scherm (voor schermlezers)
    const title = dialog.querySelector(`[data-help-view="${name}"] .help__title`)
    panel.setAttribute('aria-labelledby', title.id)
    panel.querySelector('[data-help-scroll]').scrollTop = 0
    if (focus) title.focus({ preventScroll: true })
  }

  switches.forEach((btn) => {
    btn.addEventListener('click', () => showView(btn.dataset.helpShow, { focus: true }))
  })

  // ---------- Openen en sluiten ----------------------------------------------
  async function open() {
    if (isOpen || busy) return
    busy = true
    hideHint()
    showView('guide') // altijd beginnen bij de uitleg
    const useGenie = !prefersReducedMotion()

    // Bij genie het echte paneel eerst onzichtbaar maken, anders zie je het
    // één frame "flitsen" voordat de animatie begint.
    if (useGenie) panel.style.visibility = 'hidden'
    dialog.hidden = false
    setOpenState(true)
    void dialog.offsetWidth // browser dwingen de begintoestand te tekenen, zodat de fade start
    dialog.classList.add('is-visible')

    if (useGenie) {
      await genie(panel, fab, 'out')
    } else {
      dialog.classList.add('is-entering') // zachte fade (help.css)
    }

    panel.focus({ preventScroll: true }) // toetsenbord- en schermlezergebruikers starten in het venster
    busy = false
  }

  async function close() {
    if (!isOpen || busy) return
    busy = true
    const useGenie = !prefersReducedMotion()

    dialog.classList.remove('is-visible', 'is-entering') // donkere achtergrond vervaagt (CSS)
    if (useGenie) await genie(panel, fab, 'in')

    dialog.hidden = true
    panel.style.visibility = ''
    setOpenState(false)

    if (useGenie) {
      fab.classList.remove('is-receiving')
      void fab.offsetWidth // animatie opnieuw kunnen starten
      fab.classList.add('is-receiving')
    }
    fab.focus({ preventScroll: true })
    busy = false
  }

  fab.addEventListener('click', () => (isOpen ? close() : open()))
  fab.addEventListener('animationend', () => fab.classList.remove('is-receiving'))
  dialog.querySelectorAll('[data-help-close]').forEach((el) => el.addEventListener('click', close))

  document.addEventListener('keydown', (e) => {
    if (!isOpen) return
    if (e.key === 'Escape') {
      e.preventDefault()
      close()
    } else if (e.key === 'Tab') {
      trapFocus(e, panel)
    }
  })

  // ---------- Hint naast de ?-knop -------------------------------------------
  // Werkt met een class: .is-visible laat hem in- en uitfaden (help.css).
  // Zonder die class is hij visibility: hidden, dus ook niet klikbaar of
  // bereikbaar met Tab.
  function showHint() {
    if (!hint || isOpen) return
    hint.hidden = false
    void hint.offsetWidth // begintoestand tekenen, zodat de fade-in start
    hint.classList.add('is-visible')
    hintTimer = setTimeout(hideHint, HINT_DURATION_MS)
  }

  function hideHint() {
    if (!hint) return
    clearTimeout(hintTimer)
    // Stond de toetsenbordfocus op de hint? Dan naar de ?-knop, niet "kwijt".
    if (hint.contains(document.activeElement)) fab.focus({ preventScroll: true })
    hint.classList.remove('is-visible')
  }

  hint?.querySelector('[data-help-hint-open]')?.addEventListener('click', open)
  hint?.querySelector('[data-help-hint-close]')?.addEventListener('click', hideHint)

  setTimeout(showHint, HINT_DELAY_MS)
}

// Houdt de Tab-toets binnen het venster (anders tab je naar de pagina erachter).
// Alleen zichtbare knoppen en links tellen mee: het verborgen scherm niet.
function trapFocus(e, panel) {
  const items = [...panel.querySelectorAll('a[href], button:not([disabled])')]
    .filter((el) => el.getClientRects().length > 0)
  if (items.length === 0) return
  const first = items[0]
  const last = items[items.length - 1]
  const active = document.activeElement
  if (e.shiftKey && (active === first || active === panel || !panel.contains(active))) {
    e.preventDefault()
    last.focus()
  } else if (!e.shiftKey && (active === last || !panel.contains(active))) {
    e.preventDefault()
    first.focus()
  }
}
