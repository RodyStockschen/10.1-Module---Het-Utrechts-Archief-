import { createModal } from './modal.js'

export function initPlayer() {
  const root = document.querySelector('[data-player]')
  if (!root) return

  const video       = root.querySelector('[data-video]')
  const marker      = root.querySelector('[data-marker]')
  const pins        = root.querySelectorAll('[data-pin]')
  const segments    = root.querySelectorAll('[data-segment]')
  const progress    = root.querySelector('[data-timeline-progress]')
  const hotspot     = root.querySelector('[data-hotspot]')
  const hotspotBtn  = root.querySelector('[data-hotspot-btn]')
  const hotspotLabel= root.querySelector('[data-hotspot-label]')
  const routeEls    = document.querySelectorAll('[data-route-progress]')

  const duration    = parseFloat(root.dataset.videoDuration || '183')
  const locationsRaw= JSON.parse(root.dataset.locations || '[]')
  const lang        = root.dataset.lang || 'nl'
  const openLabel   = root.dataset.openLabel || 'Open'
  const closeLabel  = root.dataset.closeLabel || 'Close'
  const archiveHead = root.dataset.archiveHeading || 'Archive'
  const yearLabel   = root.dataset.yearLabel || 'Year'

  // Volgorde = chronologisch (start_sec). Fallback: database sort.
  const route = [...locationsRaw].sort((a, b) => a.start - b.start)

  const modal = createModal({ closeLabel, archiveHead, yearLabel })

  let activeId = null
  let hotspotLocation = null

  function findLocationAt(t) {
    return locationsRaw.find((l) => t >= l.start && t < l.end) || null
  }

  function setActive(loc) {
    const id = loc ? loc.id : null
    if (id === activeId) return
    activeId = id

    pins.forEach((p) => p.classList.toggle('is-active', +p.dataset.pin === id))
    segments.forEach((s) => s.classList.toggle('is-active', +s.dataset.segment === id))

    if (loc) {
      hotspotLocation = loc
      hotspotLabel.textContent = `${openLabel}: ${loc.name}`
      hotspot.hidden = false
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
    if (progress) progress.style.width = (t / duration * 100) + '%'
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

  hotspotBtn?.addEventListener('click', async () => {
    if (!hotspotLocation) return
    video.pause()
    await modal.openForLocation(hotspotLocation, lang)
  })

  pins.forEach((pin) => {
    pin.addEventListener('dblclick', async (e) => {
      e.preventDefault()
      const id = +pin.dataset.pin
      const loc = locationsRaw.find((l) => l.id === id)
      if (loc) {
        video.pause()
        await modal.openForLocation(loc, lang)
      }
    })
  })
}
