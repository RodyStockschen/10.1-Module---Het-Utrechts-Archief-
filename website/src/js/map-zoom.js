/**
 * "Vergroot kaart": de hele plattegrond + alle pins in een groot venster.
 * - Opent op hetzelfde ingezoomde stuk als de minikaart op de hoofdpagina.
 * - Zoomen met de zoombalk, met scrollen, of met de toetsen + / − / 0.
 *   Slepen schuift de kaart (horizontaal vegen op een trackpad ook);
 *   ↺ gaat terug naar het startbeeld.
 * - Rand om de kaart: je kunt hem nooit uit beeld slepen (clampPan).
 * De pins zitten in de stage en worden visueel genormaliseerd via
 * --map-zoom-scale (inverse scale), zodat ze niet mee-groeien met de zoom.
 */
import { lockScroll, unlockScroll } from './scroll-lock.js'

export function initMapZoom() {
  const modal   = document.querySelector('[data-map-zoom-modal]')
  const openBtn = document.querySelector('[data-map-zoom]')
  if (!modal || !openBtn) return

  const viewport = modal.querySelector('[data-map-zoom-viewport]')
  const stage    = modal.querySelector('[data-map-zoom-stage]')
  const image    = modal.querySelector('[data-map-zoom-image]')
  const slider   = modal.querySelector('[data-map-zoom-slider]')
  const btnReset = modal.querySelector('[data-map-zoom-reset]')
  const closers  = modal.querySelectorAll('[data-map-zoom-close]')

  let scale = 1, tx = 0, ty = 0, baseFit = 1
  const MIN = 1, MAX = 6 // 1 = hele kaart past in het venster, 6 = 6× zo groot

  // ── Zoombalk ───────────────────────────────────────────────────────────
  // De balk loopt van 0 (hele kaart) tot 100 (6× ingezoomd). Hij werkt
  // "logaritmisch": elk stukje balk zoomt met hetzelfde percentage. Daardoor
  // voelt het schuiven overal even snel aan (van 1× naar 2× is net zo'n stuk
  // als van 3× naar 6×).
  const LOG_RANGE = Math.log(MAX / MIN)
  const sliderToZoom = (v) => MIN * Math.exp((v / 100) * LOG_RANGE)
  const zoomToSlider = (z) => (Math.log(z / MIN) / LOG_RANGE) * 100

  // Balk bijwerken naar de huidige zoom (ook na scrollen, toetsen of ↺)
  function updateSlider() {
    if (!slider) return
    const zoom = scale / baseFit
    const v = zoomToSlider(zoom)
    slider.value = String(Math.round(v))
    slider.style.setProperty('--fill', String(v / 100)) // gekleurd stuk links van het bolletje
    slider.setAttribute('aria-valuetext', `${zoom.toLocaleString(document.documentElement.lang, { maximumFractionDigits: 1 })}×`)
  }

  // Balk verschoven → zoomen rond het midden van het venster
  slider?.addEventListener('input', () => {
    zoomBy((baseFit * sliderToZoom(Number(slider.value))) / scale)
  })

  // Rand om de kaart: de kaart mag niet verder schuiven dan zijn eigen rand.
  // Het midden van de kaart staat op (midden venster + tx, ty). Is de kaart
  // breder dan het venster, dan mag hij hooguit zoveel opzij als hij uitsteekt
  // (half links, half rechts); is hij smaller, dan blijft hij in het midden.
  function clampPan() {
    const maxX = Math.max(0, (stage.offsetWidth * scale - viewport.clientWidth) / 2)
    const maxY = Math.max(0, (stage.offsetHeight * scale - viewport.clientHeight) / 2)
    tx = Math.min(maxX, Math.max(-maxX, tx))
    ty = Math.min(maxY, Math.max(-maxY, ty))
  }

  function apply() {
    clampPan()
    stage.style.transform =
      `translate(calc(-50% + ${tx}px), calc(-50% + ${ty}px)) scale(${scale})`
    modal.style.setProperty('--map-zoom-scale', String(scale))
    updateLabels()
    updateSlider()
  }

  // ── Namen bij de punten ────────────────────────────────────────────────
  // Uitgezoomd liggen de punten zo dicht bij elkaar dat de namen over elkaar
  // heen vallen (onoverzichtelijk). Daarom: namen pas tonen zodra ze elkaar
  // en de andere stippen niet meer raken. Dit past zich vanzelf aan de
  // schermgrootte aan, en ook als er via het CMS punten bijkomen.
  const pins = [...stage.querySelectorAll('.map-zoom__pin')]
  const LABEL_MARGIN = 4 // minimaal zoveel px lucht rond elke naam
  let labelsCheckedAt = null

  function updateLabels() {
    if (scale === labelsCheckedAt) return // alleen opnieuw rekenen als de zoom verandert
    labelsCheckedAt = scale
    stage.classList.toggle('labels-hidden', labelsOverlap())
  }

  function labelsOverlap() {
    // We rekenen de posities uit in plaats van ze op het scherm te meten:
    // de kaart animeert bij het zoomen, de rekensom geeft meteen de eindstand.
    const mapW = stage.offsetWidth * scale  // getekende breedte van de kaart (px)
    const mapH = stage.offsetHeight * scale

    const boxes = pins.map((pin) => {
      const x = (parseFloat(pin.style.left) / 100) * mapW // midden van de stip
      const y = (parseFloat(pin.style.top) / 100) * mapH
      const label = pin.querySelector('.map-zoom__pin-label')
      const dotR = pin.offsetWidth / 2      // de knop is precies de stip
      const top = y - pin.offsetHeight / 2
      const labelTop = top + label.offsetTop // naam hangt onder de stip
      const labelW = label.offsetWidth       // naam is horizontaal gecentreerd
      return {
        dot: { x1: x - dotR, y1: y - dotR, x2: x + dotR, y2: y + dotR },
        label: {
          x1: x - labelW / 2 - LABEL_MARGIN,
          y1: labelTop - LABEL_MARGIN,
          x2: x + labelW / 2 + LABEL_MARGIN,
          y2: labelTop + label.offsetHeight + LABEL_MARGIN,
        },
      }
    })

    const hit = (a, b) => a.x1 < b.x2 && b.x1 < a.x2 && a.y1 < b.y2 && b.y1 < a.y2
    for (let i = 0; i < boxes.length; i++) {
      for (let j = i + 1; j < boxes.length; j++) {
        const a = boxes[i], b = boxes[j]
        // naam raakt naam, of een naam valt over een andere stip
        if (hit(a.label, b.label) || hit(a.label, b.dot) || hit(b.label, a.dot)) return true
      }
    }
    return false
  }

  // Startbeeld (bij openen en bij ↺): hetzelfde ingezoomde stuk als de
  // minikaart op de hoofdpagina. Dat hele stuk past in het grote venster, met
  // hetzelfde punt in het midden; door het bredere venster zie je links en
  // rechts nog wat extra. Geen minikaart in beeld? Dan de hele kaart.
  function reset() {
    const vw = viewport.clientWidth
    const vh = viewport.clientHeight
    const iw = image.naturalWidth  || 1
    const ih = image.naturalHeight || 1
    stage.style.width  = iw + 'px'
    stage.style.height = ih + 'px'
    baseFit = Math.min(vw / iw, vh / ih) // zo past de hele kaart in het venster
    scale = baseFit
    tx = 0; ty = 0

    // minimap-view.js zet de zoom van de minikaart in --minimap-zoom
    const mini = document.querySelector('[data-minimap]')
    const miniScale = parseFloat(mini?.style.getPropertyValue('--minimap-zoom'))
    if (mini && miniScale && mini.clientWidth && mini.clientHeight) {
      const areaW = mini.clientWidth / miniScale   // het stuk dat je in de minikaart
      const areaH = mini.clientHeight / miniScale  // ziet, in pixels van de kaart
      const fitArea = Math.min(vw / areaW, vh / areaH)
      scale = Math.max(baseFit * MIN, Math.min(baseFit * MAX, fitArea))
      // Focuspunt van de minikaart (in %) naar het midden van het venster
      tx = (0.5 - parseFloat(mini.dataset.focusX) / 100) * iw * scale
      ty = (0.5 - parseFloat(mini.dataset.focusY) / 100) * ih * scale
    }

    labelsCheckedAt = null // venster kan van grootte veranderd zijn: opnieuw rekenen
    apply()
  }

  // Zoomen rond een punt op het scherm (cx, cy): de muis bij scrollen, of het
  // midden van het venster (zoombalk, toetsen). Het stukje kaart onder dat
  // punt blijft op zijn plek staan.
  // Rekensom: ox/oy = afstand van het zoompunt tot het midden van het venster.
  // De afstand tussen dat punt en het midden van de kaart (tx - ox) wordt net
  // zoveel keer groter als de kaart zelf (k).
  function zoomBy(factor, cx = null, cy = null) {
    const prev = scale
    scale = Math.max(baseFit * MIN, Math.min(baseFit * MAX, scale * factor))
    const k = scale / prev
    let ox = 0, oy = 0
    if (cx !== null && cy !== null) {
      const rect = viewport.getBoundingClientRect()
      ox = cx - rect.left - rect.width / 2
      oy = cy - rect.top - rect.height / 2
    }
    tx = ox + (tx - ox) * k
    ty = oy + (ty - oy) * k
    apply()
  }

  function open() {
    modal.hidden = false
    modal.setAttribute('aria-hidden', 'false')
    lockScroll('map-zoom') // pagina erachter vast, zonder verspringen
    if (image.complete && image.naturalWidth) reset()
    else image.addEventListener('load', reset, { once: true })
  }

  function close() {
    modal.hidden = true
    modal.setAttribute('aria-hidden', 'true')
    unlockScroll('map-zoom')
  }

  // Staat de pop-up van een locatie boven de kaart (klik op een stip, zie
  // player.js)? Dan zijn de toetsen voor de pop-up: Escape sluit alleen die.
  const popupOpen = () => document.querySelector('[data-modal]')?.hidden === false

  openBtn.addEventListener('click', open)
  closers.forEach((el) => el.addEventListener('click', close))
  document.addEventListener('keydown', (e) => {
    if (modal.hidden || popupOpen()) return
    if (e.key === 'Escape') close()
    if (e.key === '+' || e.key === '=') zoomBy(1.2)
    if (e.key === '-' || e.key === '_') zoomBy(1 / 1.2)
    if (e.key === '0') reset()
  })

  btnReset.addEventListener('click', reset)

  // Toggle pins + labels in de vergrote view.
  const togglePins = modal.querySelector('[data-map-zoom-toggle-pins]')
  const textOn     = togglePins?.querySelector('[data-toggle-text-on]')
  const textOff    = togglePins?.querySelector('[data-toggle-text-off]')
  togglePins?.addEventListener('click', () => {
    const hidden = stage.classList.toggle('pins-hidden')
    togglePins.setAttribute('aria-pressed', hidden ? 'false' : 'true')
    if (textOn)  textOn.hidden  = hidden
    if (textOff) textOff.hidden = !hidden
  })

  viewport.addEventListener('wheel', (e) => {
    e.preventDefault()
    // Trackpad: vooral opzij vegen = kaart opzij schuiven (binnen de rand),
    // niet uitzoomen.
    if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
      tx -= e.deltaX
      apply()
      return
    }
    const factor = e.deltaY < 0 ? 1.12 : 1 / 1.12
    zoomBy(factor, e.clientX, e.clientY)
  }, { passive: false })

  // drag pan — ignore if the pointerdown started on a pin button
  let dragging = false, lastX = 0, lastY = 0, startX = 0, startY = 0, moved = false
  viewport.addEventListener('pointerdown', (e) => {
    if (e.target.closest('.map-zoom__pin')) return
    dragging = true; moved = false
    lastX = startX = e.clientX
    lastY = startY = e.clientY
    viewport.setPointerCapture(e.pointerId)
    viewport.classList.add('is-panning')
  })
  viewport.addEventListener('pointermove', (e) => {
    if (!dragging) return
    if (Math.abs(e.clientX - startX) > 3 || Math.abs(e.clientY - startY) > 3) moved = true
    tx += e.clientX - lastX
    ty += e.clientY - lastY
    lastX = e.clientX; lastY = e.clientY
    apply()
  })
  viewport.addEventListener('pointerup', (e) => {
    dragging = false
    viewport.classList.remove('is-panning')
    try { viewport.releasePointerCapture(e.pointerId) } catch {}
  })

  // Klik op een pin → pop-up van die locatie. Dat regelt player.js, want
  // daar zitten de pop-up en de gegevens van de locaties.
}
