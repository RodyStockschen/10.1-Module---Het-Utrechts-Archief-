// "Zo werkt het"-venster met colofon (HTML: includes/help.php, opmaak: help.css)
//
// - Opent automatisch elke keer dat de homepage laadt (zachte fade).
// - Sluiten: het venster wordt met een genie-effect (zoals een venster op de
//   Mac in het Dock verdwijnt) de ronde ?-knop linksonder in gezogen.
// - Openen via de ?-knop: dezelfde animatie achteruit, het venster komt uit
//   de knop tevoorschijn.

const GENIE_MS = 700   // duur van de genie-animatie in milliseconden
const SLICES = 40      // aantal reepjes; meer = vloeiendere randen, maar zwaarder

// Hulpfuncties voor vloeiende bewegingen (waarde 0..1 in, 0..1 uit)
const clamp01 = (x) => Math.min(Math.max(x, 0), 1)
const smoothstep = (x) => { x = clamp01(x); return x * x * (3 - 2 * x) } // traag-snel-traag
const easeInQuad = (x) => x * x                                           // steeds sneller

export function initHelp() {
  const fab = document.querySelector('[data-help-toggle]')
  const dialog = document.querySelector('[data-help]')
  if (!fab || !dialog) return

  const panel = dialog.querySelector('[data-help-panel]')
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
  let isOpen = false
  let busy = false // voorkomt dubbelklikken tijdens een animatie

  function setOpenState(open) {
    isOpen = open
    fab.setAttribute('aria-expanded', String(open))
    fab.classList.toggle('is-open', open)
    document.body.style.overflow = open ? 'hidden' : '' // pagina erachter niet laten scrollen
  }

  async function open({ from = 'fab' } = {}) {
    if (isOpen || busy) return
    busy = true
    const useGenie = from === 'fab' && !reduceMotion.matches

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
    const useGenie = !reduceMotion.matches

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

  fab.addEventListener('click', () => (isOpen ? close() : open({ from: 'fab' })))
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

  // Altijd openen als de website wordt geopend.
  open({ from: 'load' })
}

// Houdt de Tab-toets binnen het venster (anders tab je naar de pagina erachter).
function trapFocus(e, panel) {
  const items = [...panel.querySelectorAll('a[href], button:not([disabled])')]
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

/**
 * Genie-effect.
 * direction 'in'  = venster → knop (sluiten)
 * direction 'out' = knop → venster (openen; dezelfde animatie achteruit)
 *
 * Hoe het werkt:
 * 1. We knippen het venster in SLICES dunne horizontale reepjes. Elk reepje
 *    is een kopie van het hele venster, waarvan je via overflow: hidden maar
 *    één strookje ziet.
 * 2. Er loopt een denkbeeldige "trechter" van de bovenkant van het venster
 *    naar het midden van de ?-knop. Hoe lager een reepje in die trechter zit,
 *    hoe smaller het wordt en hoe meer het naar de knop schuift.
 * 3. Fase 1 (begin): de zijkanten buigen naar de knop toe.
 *    Fase 2 (daarna): alle reepjes glijden de trechter in en worden bij de
 *    knop platgedrukt tot ze verdwijnen.
 * Per frame passen we alleen transform aan (elk reepje wordt een trapezium
 * dat precies in de trechter past). Dat kan de browser heel snel tekenen.
 */
function genie(panel, target, direction) {
  return new Promise((resolve) => {
    const p = panel.getBoundingClientRect()
    const b = target.getBoundingClientRect()
    if (p.width === 0 || p.height === 0) {
      resolve()
      return
    }

    const scroller = panel.querySelector('[data-help-scroll]')
    const scrollTop = scroller ? scroller.scrollTop : 0
    const sliceH = p.height / SLICES

    // 1. Reepjes maken
    const layer = document.createElement('div')
    layer.className = 'genie-layer'
    layer.setAttribute('aria-hidden', 'true')
    layer.inert = true

    const strips = []
    for (let i = 0; i < SLICES; i++) {
      const strip = document.createElement('div')
      strip.className = 'genie-strip'
      // +0.6px overlap zodat er geen haarfijne naden tussen de reepjes zichtbaar zijn
      strip.style.cssText = `left:${p.left}px;top:${p.top + i * sliceH}px;width:${p.width}px;height:${sliceH + 0.6}px;`

      const copy = panel.cloneNode(true)
      copy.removeAttribute('id')
      copy.querySelectorAll('[id]').forEach((el) => el.removeAttribute('id')) // geen dubbele id's
      copy.style.cssText = `position:absolute;left:0;top:${-i * sliceH}px;width:${p.width}px;height:${p.height}px;max-height:none;margin:0;visibility:visible;animation:none;transition:none;`

      strip.appendChild(copy)
      layer.appendChild(strip)
      strips.push(strip)
    }
    document.body.appendChild(layer)

    // Was de uitleg naar beneden gescrold? Dan de kopieën even ver scrollen.
    if (scrollTop) layer.querySelectorAll('[data-help-scroll]').forEach((el) => { el.scrollTop = scrollTop })
    panel.style.visibility = 'hidden' // tijdens de animatie zie je alleen de reepjes

    // 2. Trechter: van de bovenkant van het venster (top) naar het midden van de knop (endY)
    const left = p.left
    const width = p.width
    const top = p.top
    const endY = b.top + b.height / 2
    const span = Math.max(endY - top, 1)
    const tipLeft = b.left + b.width * 0.15  // smalle uiteinde iets smaller dan de knop,
    const tipRight = b.right - b.width * 0.15 // zodat het venster er echt "in" verdwijnt

    // Linker- en rechterrand van de trechter op hoogte y (bend = hoe ver gebogen, 0..1)
    const edgesAt = (y, bend) => {
      const f = smoothstep((y - top) / span) * bend
      return [left + (tipLeft - left) * f, left + width + (tipRight - (left + width)) * f]
    }

    // t = 0: gewoon venster · t = 1: helemaal in de knop
    const render = (t) => {
      const bend = smoothstep(t / 0.45)                // fase 1: zijkanten buigen
      const slide = easeInQuad(clamp01((t - 0.3) / 0.7)) // fase 2: de trechter in glijden
      const shift = slide * span

      for (let i = 0; i < SLICES; i++) {
        // Boven- en onderkant van dit reepje op het scherm
        const y0 = Math.min(top + i * sliceH + shift, endY)
        const y1 = Math.min(top + (i + 1) * sliceH + shift, endY)
        if (y1 - y0 < 0.05) { // helemaal in de knop verdwenen
          strips[i].style.transform = 'scale(0)'
          continue
        }

        // Randen van de trechter aan de boven- en onderkant van het reepje
        const [l0, r0] = edgesAt(y0, bend)
        const [l1, r1] = edgesAt(y1, bend)

        // Elk reepje wordt een trapezium: bovenkant van l0 tot r0, onderkant
        // van l1 tot r1. Omdat het volgende reepje bovenaan precies dezelfde
        // randen krijgt, sluiten ze naadloos op elkaar aan (geen trapjes).
        // Een gewone 2D-transform kan geen trapezium maken; matrix3d met een
        // "perspectief"-getal (k) wel. Coördinaten zijn ten opzichte van de
        // linkerbovenhoek van het reepje.
        const originY = top + i * sliceH
        const x0 = l0 - left
        const x3 = l1 - left
        const ya = y0 - originY
        const yb = y1 - originY
        const k = (r0 - l0) / (r1 - l1) - 1 // 0 = rechthoek, >0 = onderkant smaller

        const a = (r0 - l0) / width
        const b = (x3 - x0 + k * x3) / sliceH
        const e = (yb - ya + k * yb) / sliceH
        strips[i].style.transform =
          `matrix3d(${a},0,0,0, ${b},${e},0,${k / sliceH}, 0,0,1,0, ${x0},${ya},0,1)`
      }
      // Laatste stukje: zacht vervagen, zodat het venster niet abrupt verdwijnt
      layer.style.opacity = t > 0.85 ? String(clamp01((1 - t) / 0.15)) : '1'
    }

    // 3. Animatie: elk frame de reepjes opnieuw neerzetten
    const start = performance.now()
    const tick = (now) => {
      const progress = clamp01((now - start) / GENIE_MS)
      render(direction === 'in' ? progress : 1 - progress)
      if (progress < 1) {
        requestAnimationFrame(tick)
      } else {
        layer.remove()
        panel.style.visibility = ''
        resolve()
      }
    }
    render(direction === 'in' ? 0 : 1)
    requestAnimationFrame(tick)
  })
}
