import { genie, prefersReducedMotion } from './genie.js'
import { lockScroll, unlockScroll } from './scroll-lock.js'

// Pop-up met informatie en archiefstukken van een locatie.
// Komt de pop-up uit een knop (opts.origin: het rode label in de video of een
// stip op de kaart), dan groeit hij daar
// met een genie-effect uit en verdwijnt hij er bij sluiten weer in.
export function createModal({ closeLabel = 'Close', archiveHead = 'Archive', yearLabel = 'Year', sourceLabel = 'View source' } = {}) {
  const modal = document.querySelector('[data-modal]')
  if (!modal) {
    return {
      openForLocation: () => {},
      close: () => {},
    }
  }

  const panel     = modal.querySelector('.modal__panel')
  const titleEl   = modal.querySelector('[data-modal-title]')
  const shortEl   = modal.querySelector('[data-modal-short]')            // kindertekst
  const archiveEl = modal.querySelector('[data-modal-archive]')          // blok "Uit het archief"
  const archiveTextEl = modal.querySelector('[data-modal-archive-text]')
  const itemsEl   = modal.querySelector('[data-modal-items]')

  let origin = null   // knop waar de pop-up uit kwam (de pulserende stip)
  let onClosed = null // wordt aangeroepen als de pop-up helemaal dicht is
  let busy = false    // geen dubbele animaties tegelijk

  // Genie alleen als de knop nog zichtbaar is en "minder beweging" uit staat
  const canGenie = () =>
    origin && origin.isConnected && origin.getClientRects().length > 0 && !prefersReducedMotion()

  // De knop tijdens de animatie boven de pop-up zetten (zie .genie-target),
  // zodat je de pop-up er echt uit ziet komen en weer in ziet verdwijnen.
  const raiseOrigin = (on) => origin?.closest('[data-hotspot]')?.classList.toggle('genie-target', on)

  async function open() {
    modal.hidden = false
    modal.setAttribute('aria-hidden', 'false')
    lockScroll('modal') // pagina erachter vast, zonder verspringen
    if (canGenie()) {
      panel.style.visibility = 'hidden' // niet één frame "flitsen" voor de animatie
      void modal.offsetWidth            // begintoestand tekenen, zodat de achtergrond infadet
      modal.classList.add('is-visible')
      raiseOrigin(true)
      await genie(panel, origin, 'out')
      raiseOrigin(false)
    } else {
      void modal.offsetWidth
      modal.classList.add('is-visible')
    }
  }

  async function close() {
    if (modal.hidden || busy) return
    busy = true
    modal.classList.remove('is-visible') // donkere achtergrond vervaagt
    if (canGenie()) {
      raiseOrigin(true)
      await genie(panel, origin, 'in')
      raiseOrigin(false)
    }
    modal.hidden = true
    modal.setAttribute('aria-hidden', 'true')
    panel.style.visibility = ''
    unlockScroll('modal')

    const done = onClosed
    origin = null
    onClosed = null
    busy = false
    done?.()
  }

  modal.querySelectorAll('[data-modal-close]').forEach((el) => {
    el.addEventListener('click', close)
  })

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) close()
  })

  /**
   * @param {object} location
   * @param {string} lang
   * @param {{ origin?: HTMLElement, onClosed?: Function }} [opts]
   */
  async function openForLocation(location, lang, opts = {}) {
    if (busy || !modal.hidden) return
    busy = true
    origin = opts.origin || null
    onClosed = opts.onClosed || null

    titleEl.textContent = location.name
    // Kindertekst en archieftekst: leeg = niet tonen (plekken met alleen foto's)
    shortEl.textContent = location.short || ''
    shortEl.hidden = !location.short
    if (archiveEl && archiveTextEl) {
      archiveTextEl.textContent = location.archive || ''
      archiveEl.hidden = !location.archive
    }

    // Eerst de archiefstukken ophalen, zodat het genie-effect meteen het
    // complete venster laat zien (anders groeit het na de animatie nog door).
    try {
      const res = await fetch(`/api/archiefstukken.php?location_id=${location.id}&lang=${encodeURIComponent(lang)}`, { credentials: 'same-origin' })
      if (!res.ok) throw new Error('fetch failed')
      const data = await res.json()
      renderItems(data.items || [])
    } catch (err) {
      itemsEl.innerHTML = `<p class="archive-item__desc">Er ging iets mis bij het laden.</p>`
    }

    await open()
    busy = false
  }

  function renderItems(items) {
    // Geen foto's (bijv. bij 'Start van de route'): dan alleen de teksten tonen
    if (items.length === 0) {
      itemsEl.innerHTML = ''
      return
    }
    itemsEl.innerHTML = items.map((it) => `
      <article class="archive-item">
        <img class="archive-item__img" src="/${escapeHtml(it.image_path)}" alt="${escapeHtml(it.title)}" loading="lazy">
        <div class="archive-item__body">
          <h4>${escapeHtml(it.title)}</h4>
          ${it.year ? `<p class="archive-item__year">${escapeHtml(yearLabel)}: ${escapeHtml(String(it.year))}</p>` : ''}
          <p class="archive-item__desc">${escapeHtml(it.description)}</p>
          ${creditHtml(it)}
        </div>
      </article>
    `).join('')
  }

  // Bronvermelding onder een foto: archief, maker en licentie, plus een link
  // naar de foto in de beeldbank. De CMS laat alleen http(s)-links toe.
  function creditHtml(it) {
    if (!it.credit && !it.source_url) return ''
    const link = it.source_url
      ? `<a href="${escapeHtml(it.source_url)}" target="_blank" rel="noopener">${escapeHtml(sourceLabel)} ↗</a>`
      : ''
    // Elk deel ("CC BY-NC 4.0", de maker…) in een eigen <span>, zodat het
    // niet midden in het deel over twee regels breekt (zie modal.css).
    const credit = (it.credit || '').split(' · ').filter(Boolean)
      .map((part) => `<span>${escapeHtml(part)}</span>`).join(' · ')
    return `<p class="archive-item__credit">${credit}${credit && link ? '<br>' : ''}${link}</p>`
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]))
  }

  return { openForLocation, close }
}
