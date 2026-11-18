import '../css/main.css'
import { initDropdowns } from './dropdowns.js'
import { initSearchToggle } from './search-toggle.js'
import { initHeaderScroll } from './header-scroll.js'
import { initHelp } from './help.js'
import { initPlayer } from './player.js'
import { initMinimapView } from './minimap-view.js'
import { initMapZoom } from './map-zoom.js'

document.addEventListener('DOMContentLoaded', () => {
  initDropdowns()
  initSearchToggle()
  initHeaderScroll()
  initHelp()
  initPlayer()
  initMinimapView()
  initMapZoom()
})
