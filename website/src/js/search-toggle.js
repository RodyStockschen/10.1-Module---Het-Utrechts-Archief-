export function initSearchToggle() {
  const btn = document.querySelector('[data-search-toggle]')
  const bar = document.getElementById('site-search')
  if (!btn || !bar) return

  const iconOpen  = btn.querySelector('.search_icon')
  const iconClose = btn.querySelector('.search_close')

  btn.addEventListener('click', () => {
    const open = bar.hidden
    bar.hidden = !open
    btn.setAttribute('aria-expanded', open ? 'true' : 'false')
    if (iconOpen && iconClose) {
      iconOpen.hidden  = open
      iconClose.hidden = !open
    }
    if (open) bar.querySelector('input')?.focus()
  })
}
