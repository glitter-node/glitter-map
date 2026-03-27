import Alpine from 'alpinejs'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

const DefaultIcon = L.icon({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
})

L.Marker.prototype.options.icon = DefaultIcon

const DEFAULT_CENTER = { lat: 37.5665, lng: 126.9780 }
const DEFAULT_ZOOM = 13
const MAP_REQUEST_DEBOUNCE_MS = 250
const SHOW_MAP_POLL_INTERVAL_MS = 2000
const SHOW_MAP_POLL_TIMEOUT_MS = 60000

const coerceNumber = (value) => {
    if (value === null || value === undefined || value === '') return null
    const number = Number(value)
    return Number.isFinite(number) ? number : null
}

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')

const buildMap = (element, center, zoom) => {
    const map = L.map(element, {
        zoomControl: true,
        scrollWheelZoom: false,
    }).setView([center.lat, center.lng], zoom)

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map)

    element._placeMap = map
    return map
}

const parseMapConfig = (value) => {
    if (!value) return null

    try {
        const parsed = JSON.parse(value)
        return parsed && typeof parsed === 'object' ? parsed : null
    } catch {
        return null
    }
}

const fetchShowMapLocation = async (url) => {
    if (!url || !window.axios) return null

    const response = await window.axios.get(url, {
        withCredentials: true,
    })

    return response.data ?? null
}

const startShowMapPolling = (element, config) => {
    const pollUrl = config?.pollUrl
    if (!pollUrl || element.dataset.polling === 'true' || element._placeMap) return

    element.dataset.polling = 'true'
    const startedAt = Date.now()

    const poll = async () => {
        if (element._placeMap) return

        if (Date.now() - startedAt >= SHOW_MAP_POLL_TIMEOUT_MS) {
            delete element.dataset.polling
            return
        }

        try {
            const data = await fetchShowMapLocation(pollUrl)
            const latitude = coerceNumber(data?.latitude)
            const longitude = coerceNumber(data?.longitude)

            if (latitude !== null && longitude !== null) {
                config.marker = {
                    ...config.marker,
                    latitude,
                    longitude,
                    label: data?.name ?? config.marker?.label ?? '',
                }
                element.dataset.mapConfig = JSON.stringify(config)
                element.className = 'map-frame mt-6 h-80 overflow-hidden rounded-3xl'
                element.textContent = ''
                delete element.dataset.polling
                initializeShowMap()
                return
            }
        } catch {
        }

        window.setTimeout(poll, SHOW_MAP_POLL_INTERVAL_MS)
    }

    window.setTimeout(poll, SHOW_MAP_POLL_INTERVAL_MS)
}

const debounce = (callback, delay) => {
    let timeoutId = null
    return (...args) => {
        window.clearTimeout(timeoutId)
        timeoutId = window.setTimeout(() => callback(...args), delay)
    }
}

const createPlaceMapPage = (config = {}) => ({
    mapApiUrl: config.mapApiUrl ?? '/api/places/map',
    nearbyApiUrl: config.nearbyApiUrl ?? '',
    filters: config.filters ?? {},
    mapMode: config.mapMode ?? 'map',
    mapElementId: config.mapElementId ?? 'places-index-map',
    map: null,
    markerLayer: null,
    markers: [],
    mapLoading: false,
    mapError: null,
    nearbyLoading: false,
    nearbyState: 'idle',
    nearbyPlaces: [],
    nearbyError: null,
    userLocation: null,
    hasFitBounds: false,

    init() {
        this.$nextTick(() => {
            this.initializeIndexMap()
        })
    },

    setMapMode(mode) {
        this.mapMode = mode

        if (mode !== 'map') return

        this.$nextTick(() => {
            this.initializeIndexMap()
            window.requestAnimationFrame(() => {
                this.map?.invalidateSize()
            })
        })
    },

    initializeIndexMap() {
        const element = document.getElementById(this.mapElementId)
            || document.getElementById('places-index-map')
            || document.getElementById('place-map')
            || document.querySelector('[data-place-map]')

        if (!element) {
            this.mapError = 'Map container could not be found.'
            return
        }

        if (this.map) {
            this.map.invalidateSize()
            return
        }

        this.map = buildMap(element, DEFAULT_CENTER, DEFAULT_ZOOM)

        this.markerLayer = L.layerGroup()
        this.markerLayer.addTo(this.map)

        const loadMarkers = debounce(() => {
            this.fetchMarkers()
        }, MAP_REQUEST_DEBOUNCE_MS)

        this.map.on('moveend', loadMarkers)
        this.fetchMarkers()
    },

    async fetchMarkers() {
        if (!this.map || !this.mapApiUrl || !window.axios) return

        const bounds = this.map.getBounds()
        const params = new URLSearchParams({
            south: String(bounds.getSouth()),
            west: String(bounds.getWest()),
            north: String(bounds.getNorth()),
            east: String(bounds.getEast()),
            zoom: String(this.map.getZoom()),
        })

        if (this.filters.search) params.set('search', this.filters.search)

        this.mapLoading = true
        this.mapError = null

        try {
            const response = await window.axios.get(this.mapApiUrl, {
                params,
                withCredentials: true,
            })
            const markers = Array.isArray(response.data?.data) ? response.data.data : []
            this.renderMarkers(markers)
        } catch {
            this.mapError = 'Map markers could not be loaded. Check your connection and try again.'
            this.markers = []
        } finally {
            this.mapLoading = false
        }
    },

    renderMarkers(markers) {
        this.markers = Array.isArray(markers) ? markers : []

        if (!this.markerLayer) return

        this.markerLayer.clearLayers()

        const bounds = L.latLngBounds([])

        this.markers.forEach((marker) => {
            const latitude = coerceNumber(marker?.latitude)
            const longitude = coerceNumber(marker?.longitude)

            if (latitude === null || longitude === null) return

            const leafletMarker = L.marker([latitude, longitude]).bindPopup(
                `<strong>${escapeHtml(marker?.name)}</strong>`
            )

            this.markerLayer.addLayer(leafletMarker)
            bounds.extend([latitude, longitude])
        })

        if (!this.hasFitBounds && bounds.isValid()) {
            this.hasFitBounds = true

            if (this.markers.length === 1) {
                this.map.setView(bounds.getCenter(), 15)
            } else {
                this.map.fitBounds(bounds.pad(0.2))
            }
        }
    },

    async locateUser() {
        if (!navigator.geolocation || !this.nearbyApiUrl || !window.axios) {
            this.nearbyPlaces = []
            this.userLocation = null
            this.nearbyError = 'This device or browser does not support location access.'
            this.nearbyState = 'error'
            return
        }

        this.nearbyPlaces = []
        this.userLocation = null
        this.nearbyLoading = true
        this.nearbyError = ''
        this.nearbyState = 'loading'

        navigator.geolocation.getCurrentPosition(
            async ({ coords }) => {
                this.userLocation = {
                    latitude: coords.latitude,
                    longitude: coords.longitude,
                }

                try {
                    const response = await window.axios.get(this.nearbyApiUrl, {
                        params: {
                            latitude: coords.latitude,
                            longitude: coords.longitude,
                        },
                    })

                    this.nearbyPlaces = Array.isArray(response.data?.data) ? response.data.data : []
                    this.nearbyState = this.nearbyPlaces.length ? 'success' : 'empty'
                } catch {
                    this.nearbyError = 'Nearby places could not be loaded. Try again in a moment.'
                    this.nearbyState = 'error'
                } finally {
                    this.nearbyLoading = false
                }
            },
            (error) => {
                this.nearbyLoading = false
                this.nearbyError = error.code === error.PERMISSION_DENIED
                    ? 'Location permission was denied. Allow browser location access, then press "Use current location" again.'
                    : 'Your location could not be determined. Check location settings and try again.'
                this.nearbyState = 'error'
            },
            { enableHighAccuracy: true, timeout: 5000 }
        )
    },
})

window.placeMapPage = createPlaceMapPage
window.mapPage = createPlaceMapPage
window.memoryMapPage = createPlaceMapPage

document.addEventListener('alpine:init', () => {
    Alpine.data('placeMapPage', createPlaceMapPage)
    Alpine.data('mapPage', createPlaceMapPage)
    Alpine.data('memoryMapPage', createPlaceMapPage)
})

const initializeShowMap = () => {
    const element = document.getElementById('place-show-map')
    if (!element || element._placeMap) return

    const config = parseMapConfig(element.dataset.mapConfig)
    const marker = config?.marker
    const latitude = coerceNumber(marker?.latitude)
    const longitude = coerceNumber(marker?.longitude)

    if (latitude === null || longitude === null) {
        startShowMapPolling(element, config ?? {})
        return
    }

    const zoom = coerceNumber(config?.zoom) ?? 16
    const map = buildMap(element, { lat: latitude, lng: longitude }, zoom)
    const leafletMarker = L.marker([latitude, longitude]).addTo(map)

    if (marker?.label) {
        leafletMarker.bindPopup(`<strong>${escapeHtml(marker.label)}</strong>`)
    }

    window.requestAnimationFrame(() => {
        map.invalidateSize()
    })
}

document.addEventListener('DOMContentLoaded', () => {
    initializeShowMap()
})
