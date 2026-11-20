// Video-preview in het CMS (Locatie bewerken).
// Laat zien hoe een locatie op de site in de video en de tijdlijn komt te
// staan, terwijl je de tijden typt. Met "Start = hier" / "Eind = hier" neem
// je de tijd van de video over in de invoervelden.

// Seconden → "m:ss" (zelfde als format_time() in includes/time.php)
const formatTime = (sec) => {
  const s = Math.max(0, Math.round(sec))
  return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`
}

// "m:ss" (of gewone seconden) → seconden; null als het niet klopt
// (zelfde regels als parse_time() in includes/time.php)
const parseTime = (text) => {
  const value = String(text).trim().replace(',', '.')
  const mmss = value.match(/^(\d{1,3}):([0-5]?\d(?:\.\d+)?)$/)
  if (mmss) return Number(mmss[1]) * 60 + Number(mmss[2])
  return /^\d+(?:\.\d+)?$/.test(value) ? Number(value) : null
}

export function initTimePreview() {
  const root = document.querySelector('[data-time-preview]')
  if (!root) return

  const video      = root.querySelector('[data-time-preview-video]')
  const dot        = root.querySelector('[data-time-preview-dot]')
  const dotLabel   = root.querySelector('[data-time-preview-dot-label]')
  const timeline   = root.querySelector('[data-time-preview-timeline]')
  const playhead   = root.querySelector('[data-time-preview-playhead]')
  const nowEl      = root.querySelector('[data-time-preview-now]')
  const startInput = document.querySelector('[data-time-preview-start]')
  const endInput   = document.querySelector('[data-time-preview-end]')
  const nameInput  = document.querySelector('#name_nl')
  const others     = JSON.parse(root.dataset.others || '[]')
  let duration     = parseFloat(root.dataset.duration || '183')

  // Alle locaties: de andere uit de database + deze uit de invoervelden
  function locations() {
    const start = parseTime(startInput.value)
    const end = parseTime(endInput.value)
    const list = others.map((loc) => ({ ...loc, current: false }))
    if (start !== null && end !== null && end > start) {
      list.push({ name: nameInput?.value.trim() || 'Deze locatie', start, end, current: true })
    }
    return list
  }

  // Mini-tijdlijn opnieuw opbouwen (na elke wijziging in de tijden of naam)
  function render() {
    timeline.querySelectorAll('.time-preview__segment').forEach((el) => el.remove())

    for (const loc of locations()) {
      const seg = document.createElement('button')
      seg.type = 'button'
      seg.className = 'time-preview__segment' + (loc.current ? ' is-current' : '')
      seg.style.left = (loc.start / duration) * 100 + '%'
      seg.style.width = ((loc.end - loc.start) / duration) * 100 + '%'
      seg.textContent = loc.name
      seg.title = `${loc.name}: ${formatTime(loc.start)}–${formatTime(loc.end)}`
      seg.addEventListener('click', (e) => {
        e.stopPropagation() // niet ook de klik op de balk zelf uitvoeren
        video.currentTime = loc.start
      })
      timeline.insertBefore(seg, playhead) // lijntje blijft bovenop
    }

    // Rood randje om een veld waarin de tijd niet klopt
    startInput.classList.toggle('is-invalid', parseTime(startInput.value) === null)
    endInput.classList.toggle('is-invalid', parseTime(endInput.value) === null)
    update()
  }

  // Lijntje, "Video staat op" en de stip bijwerken voor de huidige videotijd
  function update() {
    const t = video.currentTime || 0
    playhead.style.left = Math.min(t / duration, 1) * 100 + '%'
    nowEl.textContent = formatTime(t)

    const loc = locations().find((l) => t >= l.start && t < l.end)
    dot.hidden = !loc
    if (loc) {
      dotLabel.textContent = loc.name
      dot.classList.toggle('is-current', loc.current)
    }
  }

  video.addEventListener('loadedmetadata', () => {
    if (video.duration) duration = video.duration
    render()
  })
  video.addEventListener('timeupdate', update)
  video.addEventListener('seeked', update)

  // Klik op de balk → naar precies dat moment in de video
  timeline.addEventListener('click', (e) => {
    const rect = timeline.getBoundingClientRect()
    video.currentTime = Math.min(Math.max((e.clientX - rect.left) / rect.width, 0), 1) * duration
  })

  // Typen in de tijd- of naamvelden → preview meteen bijwerken
  ;[startInput, endInput, nameInput].forEach((el) => el?.addEventListener('input', render))

  // Preview dichtgeklapt (<details>)? Dan de video pauzeren.
  root.closest('details')?.addEventListener('toggle', (e) => {
    if (!e.target.open) video.pause()
  })

  // "Start = hier" / "Eind = hier": tijd van de video overnemen (hele seconden)
  root.querySelectorAll('[data-time-preview-set]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const input = btn.dataset.timePreviewSet === 'start' ? startInput : endInput
      input.value = formatTime(video.currentTime)
      render()
    })
  })

  render()
}
