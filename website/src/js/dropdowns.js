export function initDropdowns() {
  const root = document.querySelector('[data-dropdowns]')
  if (!root) return

  const items = root.querySelectorAll('[data-dropdown]')

  function closeAll(except = null) {
    items.forEach((li) => {
      if (li === except) return
      li.classList.remove('dropdown_open')
      const btn = li.querySelector('[data-dropdown-toggle]')
      if (btn) btn.setAttribute('aria-expanded', 'false')
    })
  }

  items.forEach((li) => {
    const btn = li.querySelector('[data-dropdown-toggle]')
    if (!btn) return
    btn.addEventListener('click', (e) => {
      e.stopPropagation()
      const open = li.classList.toggle('dropdown_open')
      btn.setAttribute('aria-expanded', open ? 'true' : 'false')
      closeAll(li)
    })
  })

  document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-dropdowns]')) closeAll()
  })

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll()
  })
}
