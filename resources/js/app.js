import './bootstrap'
import './layout'

import Alpine from 'alpinejs'

import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

window.L = L
window.Alpine = Alpine

import './restaurant'
import './map.js'

Alpine.start()
