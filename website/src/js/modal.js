export function createModal({ closeLabel = 'Close', archiveHead = 'Archive', yearLabel = 'Year' } = {}) {
  const modal = document.querySelector('[data-modal]')
  if (!modal) {
    return {
      openForLocation: () => {},
      close: () => {},
    }
  }

  const titleEl = modal.querySelector('[data-modal-title]')
  const shortEl = modal.querySelector('[data-modal-short]')
  const itemsEl = modal.querySelector('[data-modal-items]')

  function open() {
    modal.hidden = false
    modal.setAttribute('aria-hidden', 'false')
    document.body.style.overflow = 'hidden'
  }

  function close() {
    modal.hidden = true
    modal.setAttribute('aria-hidden', 'true')
    document.body.style.overflow = ''
  }

  modal.querySelectorAll('[data-modal-close]').forEach((el) => {
    el.addEventListener('click', close)
  })

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) close()
  })

  async function openForLocation(location, lang) {
    titleEl.textContent = location.name
    shortEl.textContent = location.short || ''
    itemsEl.innerHTML = `<p class="archive-item__desc">…</p>`
    open()

    try {
      const res = await fetch(`/api/archiefstukken.php?location_id=${location.id}&lang=${encodeURIComponent(lang)}`, { credentials: 'same-origin' })
      if (!res.ok) throw new Error('fetch failed')
      const data = await res.json()
      renderItems(data.items || [])
    } catch (err) {
      itemsEl.innerHTML = `<p class="archive-item__desc">Er ging iets mis bij het laden.</p>`
    }
  }

  function renderItems(items) {
    if (items.length === 0) {
      itemsEl.innerHTML = `<p class="archive-item__desc">Geen archiefstukken gevonden.</p>`
      return
    }
    itemsEl.innerHTML = items.map((it) => `
      <article class="archive-item">
        <img class="archive-item__img" src="/${escapeHtml(it.image_path)}" alt="${escapeHtml(it.title)}" loading="lazy">
        <div class="archive-item__body">
          <h4>${escapeHtml(it.title)}</h4>
          ${it.year ? `<p class="archive-item__year">${escapeHtml(yearLabel)}: ${escapeHtml(String(it.year))}</p>` : ''}
          <p class="archive-item__desc">${escapeHtml(it.description)}</p>
        </div>
      </article>
    `).join('')
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]))
  }

  return { openForLocation, close }
}
