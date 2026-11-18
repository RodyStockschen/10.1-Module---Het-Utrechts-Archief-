/**
 * CMS: focus-gebied voor de minimap kiezen. De viewport heeft de aspect
 * ratio van de minimap op de homepage; wat je ziet is wat de bezoeker ziet.
 * Pan + zoom = de focus (centrum %) en zoom (vermenigvuldiging t.o.v. 'fit')
 * worden daaruit afgeleid en in hidden inputs gezet voor opslag.
 */
export function initFocusPicker() {
  const picker = document.querySelector('[data-focus-picker]')
  if (!picker) return

  const viewport = picker.querySelector('[data-focus-viewport]')
  const stage    = picker.querySelector('[data-focus-stage]')
  const image    = picker.querySelector('[data-focus-image]')
  const coordsEl = picker.querySelector('[data-focus-coords]')
  const inX      = picker.querySelector('[data-focus-input-x]')
  const inY      = picker.querySelector('[data-focus-input-y]')
  const inZoom   = picker.querySelector('[data-focus-input-zoom]')
  const btnIn    = picker.querySelector('[data-focus-in]')
  const btnOut   = picker.querySelector('[data-focus-out]')
  const btnReset = picker.querySelector('[data-focus-reset]')

  const initial = {
    x:    parseFloat(picker.dataset.initialX    || '50'),
    y:    parseFloat(picker.dataset.initialY    || '50'),
    zoom: parseFloat(picker.dataset.initialZoom || '2'),
  }

  // Scale (CSS pixels per image pixel). Translate (CSS px) offset van center.
  let scale = 1, tx = 0, ty = 0, baseFit = 1
  const ZOOM_MIN = 1, ZOOM_MAX = 6

  function apply() {
    stage.style.transform =
      `translate(calc(-50% + ${tx}px), calc(-50% + ${ty}px)) scale(${scale})`
    const fz = baseFit > 0 ? scale / baseFit : 1
    // Welke image-% staat in het midden van de viewport?
    const iw = image.naturalWidth  || 1
    const ih = image.naturalHeight || 1
    const fxPct = 50 - (tx / scale) / iw * 100
    const fyPct = 50 - (ty / scale) / ih * 100
    if (inX)    inX.value    = fxPct.toFixed(2)
    if (inY)    inY.value    = fyPct.toFixed(2)
    if (inZoom) inZoom.value = fz.toFixed(3)
    if (coordsEl) {
      coordsEl.textContent =
        `Centrum X ${fxPct.toFixed(1)}%, Y ${fyPct.toFixed(1)}% — zoom ${fz.toFixed(2)}×`
    }
  }

  function computeBaseFit() {
    const vw = viewport.clientWidth
    const vh = viewport.clientHeight
    const iw = image.naturalWidth  || 1
    const ih = image.naturalHeight || 1
    stage.style.width  = iw + 'px'
    stage.style.height = ih + 'px'
    baseFit = Math.min(vw / iw, vh / ih)
  }

  function setFocus(xPct, yPct, zoomFactor) {
    computeBaseFit()
    scale = baseFit * Math.max(ZOOM_MIN, Math.min(ZOOM_MAX, zoomFactor))
    const iw = image.naturalWidth  || 1
    const ih = image.naturalHeight || 1
    tx = (50 - xPct) / 100 * iw * scale
    ty = (50 - yPct) / 100 * ih * scale
    apply()
  }

  function zoomBy(factor, cx = null, cy = null) {
    const prev = scale
    scale = Math.max(baseFit * ZOOM_MIN, Math.min(baseFit * ZOOM_MAX, scale * factor))
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

  function init() {
    setFocus(initial.x, initial.y, initial.zoom)
  }

  if (image.complete && image.naturalWidth) init()
  else image.addEventListener('load', init, { once: true })

  window.addEventListener('resize', () => {
    const prevFit = baseFit
    computeBaseFit()
    if (prevFit > 0) scale = baseFit * (scale / prevFit)
    apply()
  })

  btnIn.addEventListener('click', (e)    => { e.preventDefault(); zoomBy(1.2) })
  btnOut.addEventListener('click', (e)   => { e.preventDefault(); zoomBy(1 / 1.2) })
  btnReset.addEventListener('click', (e) => { e.preventDefault(); setFocus(35, 52, 2.2) })

  viewport.addEventListener('wheel', (e) => {
    e.preventDefault()
    const factor = e.deltaY < 0 ? 1.1 : 1 / 1.1
    zoomBy(factor, e.clientX, e.clientY)
  }, { passive: false })

  let dragging = false, lastX = 0, lastY = 0
  viewport.addEventListener('pointerdown', (e) => {
    dragging = true
    lastX = e.clientX; lastY = e.clientY
    viewport.setPointerCapture(e.pointerId)
    viewport.classList.add('is-panning')
  })
  viewport.addEventListener('pointermove', (e) => {
    if (!dragging) return
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
}
