import L from 'leaflet'

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
                const response = await window.axios.get(`${this.mapApiUrl}?${params.toString()}`)
                const markers = Array.isArray(response.data?.data) ? response.data.data : []
                this.renderMarkers(markers)
            } catch {
                this.mapError = 'Unable to load map markers.'
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
                this.nearbyError = 'Location is unavailable.'
                return
            }

            this.nearbyLoading = true
            this.nearbyError = ''

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
                    } catch {
                        this.nearbyError = 'Unable to load nearby restaurants.'
                    } finally {
                        this.nearbyLoading = false
                    }
                },
                () => {
                    this.nearbyLoading = false
                    this.nearbyError = 'Location access was denied.'
                },
                { enableHighAccuracy: true, timeout: 5000 }
            )
        },
    }
}
