import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
})

const DEFAULT_CENTER = { lat: 37.5665, lng: 126.9780 }
const DEFAULT_ZOOM = 13
const MAP_REQUEST_DEBOUNCE_MS = 250

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

    element._restaurantMap = map
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

const debounce = (callback, delay) => {
    let timeoutId = null
    return (...args) => {
        window.clearTimeout(timeoutId)
        timeoutId = window.setTimeout(() => callback(...args), delay)
    }
}

window.restaurantMapPage = function (config = {}) {
    return {
        mapApiUrl: config.mapApiUrl ?? '',
        nearbyApiUrl: config.nearbyApiUrl ?? '',
        filters: config.filters ?? {},
        mapMode: 'list',
        map: null,
        markerLayer: null,
        markers: [],
        mapLoading: false,
        mapError: null,
        nearbyLoading: false,
        nearbyState: 'idle',
        nearbyRestaurants: [],
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
            const element = document.getElementById('restaurants-index-map')
            if (!element) return

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

            if (this.filters.category) params.set('category', this.filters.category)
            if (this.filters.search) params.set('search', this.filters.search)

            this.mapLoading = true
            this.mapError = ''

            try {
                const response = await window.axios.get('/api/restaurants/map', { params, withCredentials: true })
                const markers = Array.isArray(response.data?.data) ? response.data.data : []
                this.renderMarkers(markers)
            } catch {
                this.mapError = 'Map markers could not be loaded. Check your connection and try again.'
            } finally {
                this.mapLoading = false
            }
        },

        renderMarkers(markers) {
            this.markers = markers

            if (!this.markerLayer) return

            this.markerLayer.clearLayers()

            const bounds = L.latLngBounds([])

            markers.forEach((marker) => {
                if (coerceNumber(marker.latitude) === null || coerceNumber(marker.longitude) === null) return

                const leafletMarker = L.marker([marker.latitude, marker.longitude]).bindPopup(
                    `<strong>${escapeHtml(marker.name)}</strong>`
                )

                this.markerLayer.addLayer(leafletMarker)
                bounds.extend([marker.latitude, marker.longitude])
            })

            if (!this.hasFitBounds && bounds.isValid()) {
                this.hasFitBounds = true

                if (markers.length === 1) {
                    this.map.setView(bounds.getCenter(), 15)
                } else {
                    this.map.fitBounds(bounds.pad(0.2))
                }
            }
        },

        async locateUser() {
            if (!navigator.geolocation || !this.nearbyApiUrl || !window.axios) {
                this.nearbyRestaurants = []
                this.userLocation = null
                this.nearbyError = 'This device or browser does not support location access.'
                this.nearbyState = 'error'
                return
            }

            this.nearbyRestaurants = []
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

                        this.nearbyRestaurants = Array.isArray(response.data?.data) ? response.data.data : []
                        if (!this.nearbyRestaurants.length) {
                            this.nearbyState = 'empty'
                        } else {
                            this.nearbyState = 'success'
                        }
                    } catch {
                        this.nearbyError = 'Nearby restaurants could not be loaded. Try again in a moment.'
                        this.nearbyState = 'error'
                    } finally {
                        this.nearbyLoading = false
                    }
                },
                (error) => {
                    this.nearbyLoading = false
                    this.nearbyError = error.code === error.PERMISSION_DENIED
                        ? 'Location permission was denied. Allow browser location access, then press “Use current location” again.'
                        : 'Your location could not be determined. Check location settings and try again.'
                    this.nearbyState = 'error'
                },
                { enableHighAccuracy: true, timeout: 5000 }
            )
        },
    }
}

const initializeShowMap = () => {
    const element = document.getElementById('restaurant-show-map')
    if (!element || element._restaurantMap) return

    const config = parseMapConfig(element.dataset.mapConfig)
    const marker = config?.marker
    const latitude = coerceNumber(marker?.latitude)
    const longitude = coerceNumber(marker?.longitude)

    if (latitude === null || longitude === null) return

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
