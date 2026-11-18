/**
 * Full-map zoom modal. Toont de hele plattegrond + alle pins.
 * Scroll zoomt, drag pant, +/−/↺ knoppen voor toetsenbord.
 * De pins zitten in de stage en worden visueel genormaliseerd via
 * --map-zoom-scale (inverse scale), zodat ze niet mee-groeien met de zoom.
 */
export function initMapZoom() {
  const modal   = document.querySelector('[data-map-zoom-modal]')
  const openBtn = document.querySelector('[data-map-zoom]')
  if (!modal || !openBtn) return

  const viewport = modal.querySelector('[data-map-zoom-viewport]')
  const stage    = modal.querySelector('[data-map-zoom-stage]')
  const image    = modal.querySelector('[data-map-zoom-image]')
  const btnIn    = modal.querySelector('[data-map-zoom-in]')
  const btnOut   = modal.querySelector('[data-map-zoom-out]')
  const btnReset = modal.querySelector('[data-map-zoom-reset]')
  const closers  = modal.querySelectorAll('[data-map-zoom-close]')

  let scale = 1, tx = 0, ty = 0, baseFit = 1
  const MIN = 1, MAX = 6

  function apply() {
    stage.style.transform =
      `translate(calc(-50% + ${tx}px), calc(-50% + ${ty}px)) scale(${scale})`
    modal.style.setProperty('--map-zoom-scale', String(scale))
  }

  function reset() {
    const vw = viewport.clientWidth
    const vh = viewport.clientHeight
    const iw = image.naturalWidth  || 1
    const ih = image.naturalHeight || 1
    stage.style.width  = iw + 'px'
    stage.style.height = ih + 'px'
    baseFit = Math.min(vw / iw, vh / ih)
    scale = baseFit
    tx = 0; ty = 0
    apply()
  }

  function zoomBy(factor, cx = null, cy = null) {
    const prev = scale
    scale = Math.max(baseFit * MIN, Math.min(baseFit * MAX, scale * factor))
    if (cx !== null && cy !== null) {
      const rect = viewport.getBoundingClientRect()
      const ox = cx - rect.left - rect.width / 2
      const oy = cy - rect.top - rect.height / 2
      const ratio = scale / prev - 1
      tx -= ox * ratio
      ty -= oy * ratio
    }
    apply()
  }

  function open() {
    modal.hidden = false
    modal.setAttribute('aria-hidden', 'false')
    document.body.style.overflow = 'hidden'
    if (image.complete && image.naturalWidth) reset()
    else image.addEventListener('load', reset, { once: true })
  }

  function close() {
    modal.hidden = true
    modal.setAttribute('aria-hidden', 'true')
    document.body.style.overflow = ''
  }

  openBtn.addEventListener('click', open)
  closers.forEach((el) => el.addEventListener('click', close))
  document.addEventListener('keydown', (e) => {
    if (modal.hidden) return
    if (e.key === 'Escape') close()
    if (e.key === '+' || e.key === '=') zoomBy(1.2)
    if (e.key === '-' || e.key === '_') zoomBy(1 / 1.2)
    if (e.key === '0') reset()
  })

  btnIn.addEventListener('click', () => zoomBy(1.3))
  btnOut.addEventListener('click', () => zoomBy(1 / 1.3))
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

  // Click op een pin → video naar dat moment seeken en modal sluiten
  stage.addEventListener('click', (e) => {
    const pin = e.target.closest('.map-zoom__pin')
    if (!pin) return
    const start = parseFloat(pin.dataset.start)
    if (!isNaN(start)) {
      const video = document.querySelector('[data-video]')
      if (video) {
        video.currentTime = start
        video.play().catch(() => {})
      }
    }
    close()
  })
}
