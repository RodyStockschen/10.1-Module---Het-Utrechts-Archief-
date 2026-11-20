import '../css/cms.css'
import { initMapPicker } from './map-picker.js'
import { initFocusPicker } from './focus-picker.js'
import { initTimePreview } from './cms-time-preview.js'

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      const msg = form.getAttribute('data-confirm') || 'Weet je het zeker?'
      if (!confirm(msg)) e.preventDefault()
    })
  })
  initMapPicker()
  initFocusPicker()
  initTimePreview()
})
