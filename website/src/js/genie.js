// Genie-effect: een venster wordt (zoals op de Mac in het Dock) als door een
// trechter een knop in gezogen, of komt er juist uit.
// Gebruikt door:
//   - het uitlegvenster  → de ronde ?-knop linksonder   (help.js)
//   - de pop-up van een locatie → het rode label in de video, of de stip die
//     je aanklikt op de vergrote kaart / dubbelklikt op de minikaart (modal.js)
//
// Hoe het werkt:
// 1. We knippen het venster in SLICES dunne horizontale reepjes. Elk reepje is
//    een kopie van het hele venster, waarvan je (overflow: hidden) maar één
//    strookje ziet.
// 2. Er loopt een denkbeeldige trechter van de rand van het venster die het
//    verst van de knop af ligt, naar het midden van de knop. Hoe dichter een
//    reepje bij de knop komt, hoe smaller het wordt.
// 3. Fase 1: de zijkanten buigen naar de knop toe (en past het venster niet
//    helemaal boven/onder de knop, dan klapt het eerst wat in).
//    Fase 2: alle reepjes glijden de trechter in en verdwijnen in de knop.
// Per frame passen we alleen transform aan, dat kan de browser snel tekenen.

const DURATION = 700 // ms (hoe lang de hele animatie duurt)
const SLICES = 40    // meer = vloeiender, maar zwaarder

const clamp01 = (x) => Math.min(Math.max(x, 0), 1)
const smoothstep = (x) => { x = clamp01(x); return x * x * (3 - 2 * x) } // traag-snel-traag
const easeInQuad = (x) => x * x                                           // steeds sneller

export const prefersReducedMotion = () =>
  window.matchMedia('(prefers-reduced-motion: reduce)').matches

/**
 * @param {HTMLElement} panel     het venster
 * @param {HTMLElement} target    de knop waar het venster in/uit gaat
 * @param {'in'|'out'}  direction 'in' = venster → knop, 'out' = knop → venster
 * @returns {Promise<void>} klaar als de animatie voorbij is
 */
export function genie(panel, target, direction) {
  return new Promise((resolve) => {
    const p = panel.getBoundingClientRect()
    const b = target.getBoundingClientRect()
    if (!p.width || !p.height || !b.width) {
      resolve()
      return
    }

    // ── Trechter ─────────────────────────────────────────────────────────
    // Ligt de knop onder het midden van het venster, dan trekt de trechter
    // naar beneden; anders naar boven.
    const down = b.top + b.height / 2 >= p.top + p.height / 2
    const tipY = b.top + b.height / 2
    const farY = down ? p.top : p.bottom             // rand die het verst van de knop ligt
    const span = Math.max(Math.abs(tipY - farY), 1)  // lengte van de trechter
    // Valt het venster over de knop heen? Dan eerst inklappen tot het past.
    const squeezeTo = Math.min(1, Math.max(span - 1, 1) / p.height)
    const tipLeft = b.left + b.width * 0.15   // smalle uiteinde iets smaller dan de knop,
    const tipRight = b.right - b.width * 0.15 // zodat het venster er echt "in" verdwijnt
    const left = p.left
    const width = p.width
    const sliceH = p.height / SLICES

    // ── 1. Reepjes maken ────────────────────────────────────────────────
    const layer = document.createElement('div')
    layer.className = 'genie-layer'
    layer.setAttribute('aria-hidden', 'true')
    layer.inert = true

    const strips = []
    for (let i = 0; i < SLICES; i++) {
      const strip = document.createElement('div')
      strip.className = 'genie-strip'
      // +0.6px overlap zodat er geen haarfijne naden tussen de reepjes zichtbaar zijn
      strip.style.cssText = `left:${left}px;top:${p.top + i * sliceH}px;width:${width}px;height:${sliceH + 0.6}px;`

      const copy = panel.cloneNode(true)
      copy.removeAttribute('id')
      copy.querySelectorAll('[id]').forEach((el) => el.removeAttribute('id')) // geen dubbele id's
      copy.style.cssText = `position:absolute;left:0;top:${-i * sliceH}px;width:${width}px;height:${p.height}px;max-height:none;margin:0;visibility:visible;animation:none;transition:none;`

      strip.appendChild(copy)
      layer.appendChild(strip)
      strips.push(strip)
    }

    // In volledig scherm is alleen dat element zichtbaar: de reepjes moeten er dan ín.
    const host = document.fullscreenElement || document.webkitFullscreenElement || document.body
    host.appendChild(layer)

    // Was het venster naar beneden gescrold? Dan de kopieën even ver scrollen.
    const scrollers = [panel, ...panel.querySelectorAll('[data-help-scroll]')]
    const scrollTops = scrollers.map((el) => el.scrollTop)
    if (scrollTops.some(Boolean)) {
      layer.querySelectorAll('.genie-strip').forEach((strip) => {
        const copy = strip.firstElementChild
        const copyScrollers = [copy, ...copy.querySelectorAll('[data-help-scroll]')]
        copyScrollers.forEach((el, n) => { el.scrollTop = scrollTops[n] || 0 })
      })
    }
    panel.style.visibility = 'hidden' // tijdens de animatie zie je alleen de reepjes

    // ── 2. Rekenhulpjes ─────────────────────────────────────────────────
    // d = afstand vanaf de verre rand (0 = verre rand, span = punt bij de knop)
    const yAt = (d) => (down ? farY + d : farY - d)
    const edgesAt = (d, bend) => {
      const f = smoothstep(d / span) * bend
      return [left + (tipLeft - left) * f, left + width + (tipRight - (left + width)) * f]
    }

    // t = 0: gewoon venster · t = 1: helemaal in de knop
    const render = (t) => {
      const bend = smoothstep(t / 0.45)                       // fase 1: zijkanten buigen
      const squeeze = 1 - (1 - squeezeTo) * smoothstep(t / 0.35) // fase 1: zo nodig inklappen
      const slide = easeInQuad(clamp01((t - 0.3) / 0.7))      // fase 2: de trechter in
      const shift = slide * span

      for (let k = 0; k < SLICES; k++) {
        // k telt vanaf de verre rand; i is hetzelfde reepje maar van boven geteld
        const i = down ? k : SLICES - 1 - k
        const a0 = k * sliceH * squeeze
        const a1 = (k + 1) * sliceH * squeeze
        // Reepjes die (nog) voorbij de punt hangen niet afknippen: die klappen eerst in.
        const d0 = Math.min(a0 + shift, Math.max(span, a0))
        const d1 = Math.min(a1 + shift, Math.max(span, a1))
        if (d1 - d0 < 0.05) { // helemaal in de knop verdwenen
          strips[i].style.transform = 'scale(0)'
          continue
        }

        // Boven- en onderkant van dit reepje op het scherm, met de trechterranden daar
        const yTop = down ? yAt(d0) : yAt(d1)
        const yBottom = down ? yAt(d1) : yAt(d0)
        const [l0, r0] = edgesAt(down ? d0 : d1, bend) // bovenkant
        const [l1, r1] = edgesAt(down ? d1 : d0, bend) // onderkant

        // Het reepje wordt een trapezium: bovenkant l0→r0, onderkant l1→r1.
        // Omdat het volgende reepje precies dezelfde randen krijgt, sluiten ze
        // naadloos aan. Een gewone 2D-transform kan geen trapezium maken;
        // matrix3d met een "perspectief"-getal (k) wel. Coördinaten zijn ten
        // opzichte van de linkerbovenhoek van het reepje.
        const originY = p.top + i * sliceH
        const x0 = l0 - left
        const x3 = l1 - left
        const ya = yTop - originY
        const yb = yBottom - originY
        const persp = (r0 - l0) / (r1 - l1) - 1 // 0 = rechthoek, >0 = onderkant smaller

        const ma = (r0 - l0) / width
        const mb = (x3 - x0 + persp * x3) / sliceH
        const me = (yb - ya + persp * yb) / sliceH
        strips[i].style.transform =
          `matrix3d(${ma},0,0,0, ${mb},${me},0,${persp / sliceH}, 0,0,1,0, ${x0},${ya},0,1)`
      }
      // Laatste stukje: zacht vervagen, zodat het venster niet abrupt verdwijnt
      layer.style.opacity = t > 0.85 ? String(clamp01((1 - t) / 0.15)) : '1'
    }

    // ── 3. Animatie: elk frame de reepjes opnieuw neerzetten ─────────────
    const start = performance.now()
    const tick = (now) => {
      const progress = clamp01((now - start) / DURATION)
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
