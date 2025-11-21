<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    :id="$getId()"
    :label="$getLabel()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div
            wire:ignore
            x-data="googleMapField({
                apiKey: '{{ $getExtraAttributes()['data-api-key'] }}',
                mapId: '{{ $getId() }}_map',
                lat: '{{ $getStatePath() }}_latitude',
                lng: '{{ $getStatePath() }}_longitude'
            })"
            x-init="init"
        >
            <div 
                x-ref="searchContainer" 
                class="dark:bg-gray-700 bg-white rounded-md shadow-sm"
                style="position: relative; width: 100%; margin-bottom: 10px;"
            >
                <input 
                    x-ref="searchInput"
                    type="text" 
                    placeholder="Search for a location" 
                    class="w-full p-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600"
                    style="box-sizing: border-box;"
                >
            </div>
            <div 
                id="{{ $getId() }}_map" 
                class="border border-gray-300 dark:border-gray-600 rounded-md overflow-hidden"
                style="height: 400px; width: 100%;"
            ></div>
            <input type="hidden" x-model="lat" :name="lat">
            <input type="hidden" x-model="lng" :name="lng">
        </div>
    </div>
</x-dynamic-component>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('googleMapField', ({ apiKey, mapId, lat, lng }) => ({
        lat: null,
        lng: null,
        map: null,
        marker: null,
        searchBox: null,
        init() {
            this.loadGoogleMapsScript();
        },
        loadGoogleMapsScript() {
            if (typeof google === 'undefined') {
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initGoogleMap`;
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
                window.initGoogleMap = this.initMap.bind(this);
            } else {
                this.initMap();
            }
        },
        initMap() {
            const mapElement = document.getElementById(mapId);
            const searchInput = this.$refs.searchInput;

            if (mapElement) {
                // Dark mode styles for the map
                const darkStyles = [
                    { elementType: "geometry", stylers: [{ color: "#242f3e" }] },
                    { elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
                    { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
                    {
                        featureType: "administrative.locality",
                        elementType: "labels.text.fill",
                        stylers: [{ color: "#d59563" }]
                    },
                    {
                        featureType: "poi",
                        elementType: "labels.text.fill",
                        stylers: [{ color: "#d59563" }]
                    },
                    {
                        featureType: "poi.park",
                        elementType: "geometry",
                        stylers: [{ color: "#263c3f" }]
                    },
                    {
                        featureType: "poi.park",
                        elementType: "labels.text.fill",
                        stylers: [{ color: "#6b9a76" }]
                    },
                    {
                        featureType: "road",
                        elementType: "geometry",
                        stylers: [{ color: "#38414e" }]
                    },
                    {
                        featureType: "road",
                        elementType: "geometry.stroke",
                        stylers: [{ color: "#212a37" }]
                    },
                    {
                        featureType: "road",
                        elementType: "labels.text.fill",
                        stylers: [{ color: "#9ca5b3" }]
                    },
                    {
                        featureType: "road.highway",
                        elementType: "geometry",
                        stylers: [{ color: "#746855" }]
                    },
                    {
                        featureType: "road.highway",
                        elementType: "geometry.stroke",
                        stylers: [{ color: "#1f2835" }]
                    },
                    {
                        featureType: "road.highway",
                        elementType: "labels.text.fill",
                        stylers: [{ color: "#f3d19c" }]
                    },
                    {
                        featureType: "transit",
                        elementType: "geometry",
                        stylers: [{ color: "#2f3948" }]
                    },
                    {
                        featureType: "transit.station",
                        elementType: "labels.text.fill",
                        stylers: [{ color: "#d59563" }]
                    },
                    {
                        featureType: "water",
                        elementType: "geometry",
                        stylers: [{ color: "#17263c" }]
                    },
                    {
                        featureType: "water",
                        elementType: "labels.text.fill",
                        stylers: [{ color: "#515c6d" }]
                    },
                    {
                        featureType: "water",
                        elementType: "labels.text.stroke",
                        stylers: [{ color: "#17263c" }]
                    }
                ];

                // Determine if dark mode is active
                const isDarkMode = document.documentElement.classList.contains('dark');

                // Create map with conditional styling
                this.map = new google.maps.Map(mapElement, {
                    center: { lat: 30.0508679032884, lng: 31.259472303487346 },
                    zoom: 8,
                    styles: isDarkMode ? darkStyles : null
                });

                // Create Places Autocomplete
                this.searchBox = new google.maps.places.Autocomplete(searchInput);
                this.searchBox.setFields(['geometry']);

                // Add listener for place selection
                this.searchBox.addListener('place_changed', () => {
                    const place = this.searchBox.getPlace();
                    
                    if (!place.geometry) {
                        console.log('No details available for input: ' + place.name);
                        return;
                    }

                    // Center map on selected place
                    if (place.geometry.viewport) {
                        this.map.fitBounds(place.geometry.viewport);
                    } else {
                        this.map.setCenter(place.geometry.location);
                        this.map.setZoom(17);
                    }

                    // Place a marker
                    const location = place.geometry.location;
                    this.placeMarker(location);
                });

                // Add map click listener
                this.map.addListener('click', (event) => {
                    this.placeMarker(event.latLng);
                });

                // Initial marker placement if coordinates exist
                const initialLat = parseFloat(document.getElementById('data.lat').value);
                const initialLng = parseFloat(document.getElementById('data.lng').value);

                if (!isNaN(initialLat) && !isNaN(initialLng)) {
                    const newPosition = { lat: initialLat, lng: initialLng };
                    this.marker = new google.maps.Marker({
                        position: newPosition,
                        map: this.map
                    });
                    this.map.setCenter(newPosition);
                }
            } else {
                console.error('Map element not found:', mapId);
            }
        },
        placeMarker(location) {
            const latValue = location.lat();
            const lngValue = location.lng();

            // Update hidden inputs
            let lngItem = document.getElementById("data.lng");
            let latItem = document.getElementById("data.lat");

            lngItem.value = lngValue;
            latItem.value = latValue;

            // Dispatch input and change events
            let inputEvent = new Event('input', { bubbles: true });
            let changeEvent = new Event('change', { bubbles: true });

            lngItem.dispatchEvent(inputEvent);
            lngItem.dispatchEvent(changeEvent);

            latItem.dispatchEvent(inputEvent);
            latItem.dispatchEvent(changeEvent);

            // Notify Livewire of the new values
            this.$wire.set('data.lng', lngValue);
            this.$wire.set('data.lat', latValue);

            // Update or create marker
            if (this.marker) {
                this.marker.setPosition(location);
            } else {
                this.marker = new google.maps.Marker({
                    position: location,
                    map: this.map
                });
            }

            this.lat = latValue;
            this.lng = lngValue;

            // Dispatch custom event with coordinates
            this.$dispatch('coordinates-updated', { 
                lat: latValue, 
                lng: lngValue 
            });
        }
    }));
});
</script>
@endpush