/**
 * Minimap: toon de volledige plattegrond ingezoomd op een focus-gebied
 * (data-focus-x, data-focus-y in %, data-focus-zoom als vermenigvuldigings-
 * factor t.o.v. de 'fit' schaal). Pins zitten in de stage en worden
 * automatisch meegeschaald; hun zichtbare grootte wordt gecompenseerd
 * via CSS var --minimap-zoom.
 */
export function initMinimapView() {
  const frame = document.querySelector('[data-minimap]')
  if (!frame) return

  const stage = frame.querySelector('[data-minimap-stage]')
  const image = frame.querySelector('[data-minimap-image]')
  if (!stage || !image) return

  const focusX    = parseFloat(frame.dataset.focusX   || '50')
  const focusY    = parseFloat(frame.dataset.focusY   || '50')
  const focusZoom = parseFloat(frame.dataset.focusZoom || '1')

  function layout() {
    const vw = frame.clientWidth
    const vh = frame.clientHeight
    const iw = image.naturalWidth  || 1
    const ih = image.naturalHeight || 1
    if (!iw || !ih || !vw || !vh) return

    // Stage neemt de natuurlijke grootte van de image aan, zodat percentages
    // van pins altijd correct op de image vallen.
    stage.style.width  = iw + 'px'
    stage.style.height = ih + 'px'

    // 'fit' = schaal waarop de volledige image past in het frame
    const fit = Math.min(vw / iw, vh / ih)
    const scale = fit * focusZoom

    // Translate: breng het focuspunt van de image naar het midden van het frame.
    // In schaalruimte verschuift het midden van de image met:
    //   dx = (50 - focusX)/100 * iw * scale
    //   dy = (50 - focusY)/100 * ih * scale
    const dx = ((50 - focusX) / 100) * iw * scale
    const dy = ((50 - focusY) / 100) * ih * scale

    stage.style.transform =
      `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px)) scale(${scale})`

    // Zet CSS var zodat pins/marker hun scale kunnen compenseren
    frame.style.setProperty('--minimap-zoom', String(scale))
  }

  if (image.complete && image.naturalWidth) layout()
  else image.addEventListener('load', layout, { once: true })

  window.addEventListener('resize', layout)
}
