/**
 * CMS: visueel X/Y% picken op de volledige plattegrond.
 * Klik zet marker + vult inputs. Scroll zoomt, slepen pant.
 * Structuur: viewport > stage (transformed) > image + marker.
 */
export function initMapPicker() {
  const picker = document.querySelector('[data-map-picker]')
  if (!picker) return

  const viewport = picker.querySelector('[data-map-picker-viewport]')
  const stage    = picker.querySelector('[data-map-picker-stage]')
  const image    = picker.querySelector('[data-map-picker-image]')
  const marker   = picker.querySelector('[data-map-picker-marker]')
  const coordsEl = picker.querySelector('[data-map-picker-coords]')
  // Inputs leven buiten het picker-div (in sibling .cms-form__row's).
  const form     = picker.closest('form')
  const xInput   = form?.querySelector('[data-map-picker-x]')
  const yInput   = form?.querySelector('[data-map-picker-y]')
  const btnIn    = picker.querySelector('[data-map-picker-in]')
  const btnOut   = picker.querySelector('[data-map-picker-out]')
  const btnReset = picker.querySelector('[data-map-picker-reset]')

  // Stage size = image natural size; wordt gescaled via transform.
  let scale = 1, tx = 0, ty = 0, baseFit = 1
  const MIN = 1, MAX = 6
  let dragMoved = false

  function apply() {
    stage.style.transform =
      `translate(calc(-50% + ${tx}px), calc(-50% + ${ty}px)) scale(${scale})`
  }

  // Kaart passend in het venster zetten. Dat kan alleen als de kaart zichtbaar
  // is: in een dichtgeklapt blok (<details>) is hij 0 breed. Geeft true terug
  // als het gelukt is.
  let fitted = false
  function fit() {
    const vw = viewport.clientWidth
    const vh = viewport.clientHeight
    if (!vw || !vh) return false // nog dichtgeklapt: straks opnieuw proberen
    const iw = image.naturalWidth  || 1
    const ih = image.naturalHeight || 1
    stage.style.width  = iw + 'px'
    stage.style.height = ih + 'px'
    baseFit = Math.min(vw / iw, vh / ih)
    scale = baseFit
    tx = 0; ty = 0
    apply()
    return true
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

  function updateCoords(xPct, yPct) {
    xPct = Math.max(0, Math.min(100, xPct))
    yPct = Math.max(0, Math.min(100, yPct))
    marker.style.left = xPct + '%'
    marker.style.top  = yPct + '%'
    if (xInput) xInput.value = xPct.toFixed(2)
    if (yInput) yInput.value = yPct.toFixed(2)
    if (coordsEl) coordsEl.textContent = `X ${xPct.toFixed(1)}%, Y ${yPct.toFixed(1)}%`
  }

  if (image.complete && image.naturalWidth) fitted = fit()
  else image.addEventListener('load', () => { fitted = fit() }, { once: true })

  // Zit de kaart in een inklapbaar blok? Dan bij het (eerste keer) openklappen
  // passend maken. Daarna onthoudt hij je zoom als je dicht- en openklapt.
  picker.closest('details')?.addEventListener('toggle', (e) => {
    if (e.target.open && !fitted && image.naturalWidth) fitted = fit()
  })

  window.addEventListener('resize', () => {
    const vw = viewport.clientWidth, vh = viewport.clientHeight
    if (!vw || !vh) return                 // dichtgeklapt: niets te doen
    if (!fitted) { fitted = fit(); return } // nog nooit passend gemaakt
    // al passend gemaakt → zoomniveau behouden, alleen baseFit herberekenen
    const ratio = scale / baseFit
    const iw = image.naturalWidth || 1, ih = image.naturalHeight || 1
    baseFit = Math.min(vw / iw, vh / ih)
    scale = baseFit * ratio
    apply()
  })

  btnIn.addEventListener('click', (e) => { e.preventDefault(); zoomBy(1.3) })
  btnOut.addEventListener('click', (e) => { e.preventDefault(); zoomBy(1 / 1.3) })
  btnReset.addEventListener('click', (e) => { e.preventDefault(); fit() })

  viewport.addEventListener('wheel', (e) => {
    e.preventDefault()
    const factor = e.deltaY < 0 ? 1.12 : 1 / 1.12
    zoomBy(factor, e.clientX, e.clientY)
  }, { passive: false })

  // drag pan
  let dragging = false, lastX = 0, lastY = 0, startX = 0, startY = 0
  viewport.addEventListener('pointerdown', (e) => {
    dragging = true
    dragMoved = false
    lastX = startX = e.clientX
    lastY = startY = e.clientY
    viewport.setPointerCapture(e.pointerId)
    viewport.classList.add('is-panning')
  })
  viewport.addEventListener('pointermove', (e) => {
    if (!dragging) return
    if (Math.abs(e.clientX - startX) > 3 || Math.abs(e.clientY - startY) > 3) dragMoved = true
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

  // click → marker plaatsen (als geen drag). We rekenen klik → stage coords om.
  viewport.addEventListener('click', (e) => {
    if (dragMoved) return
    const rect = image.getBoundingClientRect()
    const relX = e.clientX - rect.left
    const relY = e.clientY - rect.top
    if (rect.width === 0 || rect.height === 0) return
    const xPct = (relX / rect.width) * 100
    const yPct = (relY / rect.height) * 100
    updateCoords(xPct, yPct)
  })

  // input velden → marker bijwerken
  const syncFromInputs = () => {
    const xv = parseFloat(xInput?.value || '0')
    const yv = parseFloat(yInput?.value || '0')
    if (!isNaN(xv) && !isNaN(yv)) updateCoords(xv, yv)
  }
  xInput?.addEventListener('input', syncFromInputs)
  yInput?.addEventListener('input', syncFromInputs)
}
