// Volledig scherm voor de video, MÉT de pulserende stip en de pop-up erbij.
//
// Waarom een eigen knop? De volledig-scherm-knop van de browser maakt alléén
// het <video>-element groot. Alles wat eroverheen ligt (onze stip) en de
// pop-up vallen dan weg. Daarom maken we de hele omlijsting
// (.player__video-wrap) groot. In Chrome/Edge is de browserknop verborgen met
// controlsList="nofullscreen" (index.php). Firefox/Safari tonen hem nog wel;
// die maakt dan alleen de video groot, zonder stip.
export function initVideoFullscreen() {
  const wrap = document.querySelector('.player__video-wrap')
  const btn = wrap?.querySelector('[data-fullscreen-toggle]')
  if (!wrap || !btn) return

  // Sommige apparaten (zoals de iPhone) kunnen alleen de video zelf groot maken.
  // Dan tonen we onze knop niet; de normale videoknop werkt daar nog wel.
  if (!document.fullscreenEnabled && !document.webkitFullscreenEnabled) {
    btn.hidden = true
    return
  }

  // De pop-up staat normaal ergens anders op de pagina. We onthouden waar,
  // zodat we hem na volledig scherm weer terug kunnen zetten.
  const modal = document.querySelector('[data-modal]')
  const modalHome = modal ? { parent: modal.parentNode, next: modal.nextSibling } : null

  // "webkit"-varianten zijn voor oudere Safari-versies
  const fullscreenElement = () => document.fullscreenElement || document.webkitFullscreenElement
  const enter = () => (wrap.requestFullscreen ? wrap.requestFullscreen() : wrap.webkitRequestFullscreen())
  const exit = () => (document.exitFullscreen ? document.exitFullscreen() : document.webkitExitFullscreen())

  btn.addEventListener('click', () => {
    const result = fullscreenElement() === wrap ? exit() : enter()
    result?.catch?.(() => {}) // geweigerd door de browser? Dan gebeurt er gewoon niets.
  })

  // Wordt ook aangeroepen als je met Esc uit volledig scherm gaat.
  function onFullscreenChange() {
    const isFull = fullscreenElement() === wrap
    wrap.classList.toggle('is-fullscreen', isFull)

    const label = isFull ? btn.dataset.labelExit : btn.dataset.labelEnter
    btn.setAttribute('aria-label', label)
    btn.title = label

    // In volledig scherm is alleen de omlijsting zichtbaar. De pop-up moet er
    // daarom tijdelijk ín staan, anders zie je hem niet als je op de stip klikt.
    if (!modal) return
    if (isFull) {
      wrap.appendChild(modal)
    } else if (modal.parentNode === wrap) {
      modalHome.parent.insertBefore(modal, modalHome.next)
    }
  }

  document.addEventListener('fullscreenchange', onFullscreenChange)
  document.addEventListener('webkitfullscreenchange', onFullscreenChange)
}
