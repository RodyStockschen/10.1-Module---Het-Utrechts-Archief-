import { createModal } from './modal.js'
import { prefersReducedMotion } from './genie.js'

export function initPlayer() {
  const root = document.querySelector('[data-player]')
  if (!root) return

  const video       = root.querySelector('[data-video]')
  const marker      = root.querySelector('[data-marker]')
  const pins        = root.querySelectorAll('[data-pin]')
  const segments    = root.querySelectorAll('[data-segment]')
  const track       = root.querySelector('[data-timeline]')
  const playhead    = root.querySelector('[data-timeline-playhead]')
  const hotspot     = root.querySelector('[data-hotspot]')
  const hotspotBtn  = root.querySelector('[data-hotspot-btn]')
  const hotspotLabel= root.querySelector('[data-hotspot-label]')
  const routeEls    = document.querySelectorAll('[data-route-progress]')

  const duration    = parseFloat(root.dataset.videoDuration || '183')
  const locationsRaw= JSON.parse(root.dataset.locations || '[]')
  const lang        = root.dataset.lang || 'nl'
  const closeLabel  = root.dataset.closeLabel || 'Close'
  const archiveHead = root.dataset.archiveHeading || 'Archive'
  const yearLabel   = root.dataset.yearLabel || 'Year'
  const sourceLabel = root.dataset.sourceLabel || 'View source' // link naar de foto in het archief

  // Volgorde = chronologisch (start_sec). Fallback: database sort.
  const route = [...locationsRaw].sort((a, b) => a.start - b.start)

  const modal = createModal({ closeLabel, archiveHead, yearLabel, sourceLabel })

  let activeId = null
  let hotspotLocation = null

  function findLocationAt(t) {
    return locationsRaw.find((l) => t >= l.start && t < l.end) || null
  }

  // Nieuwe plek in beeld: het label snel laten infaden (0,25 s), iets van
  // onderen omhoog. element.animate() = een CSS-animatie, maar vanuit
  // JavaScript, zodat hij bij elke nieuwe plek opnieuw start.
  function fadeInHotspot() {
    if (prefersReducedMotion()) return
    hotspot.animate(
      [
        { opacity: 0, transform: 'translateY(8px)' },
        { opacity: 1, transform: 'none' },
      ],
      { duration: 250, easing: 'ease-out' }
    )
  }

  function setActive(loc) {
    const id = loc ? loc.id : null
    if (id === activeId) return
    activeId = id

    pins.forEach((p) => p.classList.toggle('is-active', +p.dataset.pin === id))
    segments.forEach((s) => s.classList.toggle('is-active', +s.dataset.segment === id))

    // Rood label in de video: alleen de naam van de plek (of verbergen).
    // Dat de knop een venster opent, vertelt aria-haspopup="dialog" (index.php)
    // aan schermlezers.
    if (loc) {
      hotspotLocation = loc
      hotspotLabel.textContent = loc.name
      hotspot.hidden = false
      fadeInHotspot()
    } else {
      hotspotLocation = null
      hotspot.hidden = true
    }
  }

  function routePosAt(t) {
    if (route.length === 0) return null
    if (t <= route[0].start) return { segIdx: 0, x: route[0].x, y: route[0].y }
    for (let i = 0; i < route.length - 1; i++) {
      const a = route[i], b = route[i + 1]
      if (t >= a.start && t < b.start) {
        const span = b.start - a.start
        const r = span > 0 ? (t - a.start) / span : 0
        return { segIdx: i, x: a.x + (b.x - a.x) * r, y: a.y + (b.y - a.y) * r }
      }
    }
    const last = route[route.length - 1]
    return { segIdx: route.length - 1, x: last.x, y: last.y }
  }

  function updateRoute(t) {
    if (routeEls.length === 0) return
    const pos = routePosAt(t)
    if (!pos) return
    // Progress polyline: alle vertices tot nu + huidige gelerp'te positie
    const pts = route.slice(0, pos.segIdx + 1).map((p) => `${p.x.toFixed(2)},${p.y.toFixed(2)}`)
    pts.push(`${pos.x.toFixed(2)},${pos.y.toFixed(2)}`)
    const pointsAttr = pts.join(' ')
    routeEls.forEach((el) => el.setAttribute('points', pointsAttr))

    if (marker) {
      marker.style.left = pos.x + '%'
      marker.style.top  = pos.y + '%'
      marker.classList.add('is-visible')
    }
  }

  video.addEventListener('timeupdate', () => {
    const t = video.currentTime
    // Verticaal lijntje in de tijdlijn: precies waar de film nu is
    if (playhead) playhead.style.left = (Math.min(t / duration, 1) * 100) + '%'
    setActive(findLocationAt(t))
    updateRoute(t)
  })

  // Initiële positie direct zetten (zodat de marker zichtbaar is voor play)
  video.addEventListener('loadedmetadata', () => updateRoute(video.currentTime || 0))
  updateRoute(0)

  video.addEventListener('ended', () => {
    setActive(null)
    updateRoute(duration)
  })

  pins.forEach((pin) => {
    pin.addEventListener('click', () => {
      const start = parseFloat(pin.dataset.start)
      video.currentTime = start
      video.play().catch(() => {})
    })
  })

  segments.forEach((seg) => {
    seg.addEventListener('click', () => {
      const start = parseFloat(seg.dataset.start)
      video.currentTime = start
      video.play().catch(() => {})
    })
  })

  // Tijd (in seconden) die hoort bij een plek (x) op de tijdlijn
  function timeAtX(clientX) {
    const rect = track.getBoundingClientRect()
    const ratio = Math.min(Math.max((clientX - rect.left) / rect.width, 0), 1)
    return ratio * duration
  }

  // Klik op een leeg (lichtrood) stuk van de tijdlijn → naar precies dat moment.
  // De locatieknoppen hebben hun eigen klik (naar het begin van die locatie).
  track?.addEventListener('click', (e) => {
    if (e.target.closest('[data-segment]')) return
    video.currentTime = timeAtX(e.clientX)
    video.play().catch(() => {})
  })

  // Slepen over de tijdlijn: de film en het witte lijntje lopen mee met je
  // muis of vinger; loslaten = verder afspelen vanaf daar.
  // Pas na een paar pixels beweging telt het als slepen; anders blijft het
  // een gewone klik (zie hierboven).
  const DRAG_START_PX = 4
  let drag = null             // { startX, active } zolang de muis/vinger ingedrukt is
  let ignoreNextClick = false

  function endDrag() {
    if (drag?.active) {
      ignoreNextClick = true // de klik die de browser na het loslaten stuurt
      track.classList.remove('is-dragging')
      video.play().catch(() => {})
    }
    drag = null
  }

  track?.addEventListener('pointerdown', (e) => {
    if (e.button !== 0) return // alleen linkermuisknop, vinger of pen
    drag = { startX: e.clientX, active: false }
    ignoreNextClick = false
  })

  track?.addEventListener('pointermove', (e) => {
    if (!drag) return
    if (e.buttons === 0) return endDrag() // buiten de balk losgelaten
    if (!drag.active) {
      if (Math.abs(e.clientX - drag.startX) < DRAG_START_PX) return
      drag.active = true
      track.setPointerCapture(e.pointerId) // blijven volgen, ook buiten de balk
      track.classList.add('is-dragging')
      video.pause()
    }
    const t = timeAtX(e.clientX)
    if (playhead) playhead.style.left = (t / duration) * 100 + '%' // lijntje meteen mee
    video.currentTime = t
  })

  track?.addEventListener('pointerup', endDrag)
  track?.addEventListener('pointercancel', endDrag)

  // Na het slepen stuurt de browser nog een klik. Die vangen we hier af:
  // capture (true) = deze luisteraar komt vóór die van de locatieknoppen,
  // anders zou je na het slepen naar het begin van een locatie springen.
  track?.addEventListener('click', (e) => {
    if (!ignoreNextClick) return
    ignoreNextClick = false
    e.stopPropagation()
  }, true)

  // Klik op het rode label: film pauzeren en de pop-up met een genie-effect
  // uit het label laten komen. Bij sluiten gaat hij er weer in.
  hotspotBtn?.addEventListener('click', async () => {
    if (!hotspotLocation) return
    video.pause()
    await modal.openForLocation(hotspotLocation, lang, { origin: hotspotBtn })
  })

  // Knop linksboven in de video: de info (rode labels) verbergen of weer
  // tonen, voor wie de film zonder labels wil kijken. We zetten alleen een class op
  // de omlijsting; player.css verbergt dan de stip en wisselt het icoon.
  const videoWrap    = root.querySelector('.player__video-wrap')
  const waypointsBtn = root.querySelector('[data-waypoints-toggle]')
  waypointsBtn?.addEventListener('click', () => {
    const hide = !videoWrap.classList.contains('waypoints-hidden')
    videoWrap.classList.toggle('waypoints-hidden', hide)
    // Tooltip en voorleestekst zeggen wat een klik nu gaat doen
    const label = hide ? waypointsBtn.dataset.labelShow : waypointsBtn.dataset.labelHide
    waypointsBtn.setAttribute('aria-label', label)
    waypointsBtn.title = label
  })

  // Vergrote kaart ("Vergroot kaart"): klik op een locatie → dezelfde pop-up
  // als bij het label in de video, met het genie-effect uit die stip.
  // De kaart blijft open: sluit je de pop-up, dan ben je weer op de kaart.
  // (De kaart staat buiten de speler in de HTML, daarom document.querySelectorAll.)
  document.querySelectorAll('.map-zoom__pin[data-location-id]').forEach((pin) => {
    pin.addEventListener('click', async () => {
      const loc = locationsRaw.find((l) => l.id === +pin.dataset.locationId)
      if (!loc) return
      video.pause()
      await modal.openForLocation(loc, lang, { origin: pin })
    })
  })

  // Minikaart naast de video: dubbelklik op een stip → ook de pop-up, met het
  // genie-effect uit die stip.
  pins.forEach((pin) => {
    pin.addEventListener('dblclick', async (e) => {
      e.preventDefault()
      const id = +pin.dataset.pin
      const loc = locationsRaw.find((l) => l.id === id)
      if (loc) {
        video.pause()
        await modal.openForLocation(loc, lang, { origin: pin })
      }
    })
  })
}
