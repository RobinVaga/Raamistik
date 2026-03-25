<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea'
import { Pencil, Trash2 } from 'lucide-vue-next';

interface Marker {
    id: number;
    name: string;
    lat: number;
    lng: number;
    description: string | null;
    created_at: string;
    updated_at: string;
}

const mapEl = ref<HTMLElement | null>(null);
const mapInstance = ref<L.Map | null>(null);
const markersLayer = ref<L.LayerGroup | null>(null);

const selectedLocation = ref<{ lat: number; lng: number }>();
const editingMarker = ref<Marker | null>(null);
const showDialog = ref(false);
const formName = ref('');
const formDescription = ref('');

const mapClick = (e: L.LeafletMouseEvent) => {
    editingMarker.value = null;
    formName.value = '';
    formDescription.value = '';
    selectedLocation.value = { lat: e.latlng.lat, lng: e.latlng.lng };
    showDialog.value = true;
};

const handleSubmit = (event: Event) => {
    event.preventDefault();
    const formData = new FormData(event.target as HTMLFormElement);
    
    if (editingMarker.value) {
        // Update existing marker
        router.put(`/markers/${editingMarker.value.id}`, {
            name: formData.get('name'),
            lat: editingMarker.value.lat,
            lng: editingMarker.value.lng,
            description: formData.get('description'),
        }, {
            onSuccess: () => {
                showDialog.value = false;
                editingMarker.value = null;
                selectedLocation.value = undefined;
                formName.value = '';
                formDescription.value = '';
                loadMarkers();
            }
        });
    } else {
        // Create new marker
        router.post('/markers', {
            name: formData.get('name'),
            lat: selectedLocation.value?.lat,
            lng: selectedLocation.value?.lng,
            description: formData.get('description'),
        }, {
            onSuccess: () => {
                showDialog.value = false;
                selectedLocation.value = undefined;
                formName.value = '';
                formDescription.value = '';
                loadMarkers();
            }
        });
    }
};

const editMarker = (marker: Marker) => {
    editingMarker.value = marker;
    selectedLocation.value = { lat: marker.lat, lng: marker.lng };
    formName.value = marker.name;
    formDescription.value = marker.description || '';
    showDialog.value = true;
};

const deleteMarker = (markerId: number) => {
    if (confirm('Kas oled kindel, et soovid selle markeri kustutada?')) {
        router.delete(`/markers/${markerId}`, {
            onSuccess: () => {
                loadMarkers();
            }
        });
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('et-EE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const loadMarkers = async () => {
    try {
        const response = await fetch('/api/markers');
        const markers: Marker[] = await response.json();
        
        if (markersLayer.value) {
            markersLayer.value.clearLayers();
        }
        
        markers.forEach((marker) => {
            if (mapInstance.value && markersLayer.value) {
                const leafletMarker = L.marker([marker.lat, marker.lng])
                    .bindPopup(`
                        <div class="p-3 min-w-[280px]">
                            <h3 class="font-bold text-lg mb-2">${marker.name}</h3>
                            ${marker.description ? `<p class="text-sm text-gray-600 mb-3">${marker.description}</p>` : ''}
                            <div class="text-xs text-gray-500 mb-3 space-y-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">Lat:</span>
                                    <span>${marker.lat.toFixed(6)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Lng:</span>
                                    <span>${marker.lng.toFixed(6)}</span>
                                </div>
                                <div class="flex justify-between pt-1 border-t border-gray-200">
                                    <span class="font-medium">Lisatud:</span>
                                    <span>${formatDate(marker.created_at)}</span>
                                </div>
                                ${marker.created_at !== marker.updated_at ? `
                                <div class="flex justify-between">
                                    <span class="font-medium">Muudetud:</span>
                                    <span>${formatDate(marker.updated_at)}</span>
                                </div>
                                ` : ''}
                            </div>
                            <div class="flex gap-2 mt-3">
                                <button 
                                    onclick="window.editMarker(${marker.id})"
                                    class="flex-1 flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/>
                                        <path d="m15 5 4 4"/>
                                    </svg>
                                    Muuda
                                </button>
                                <button 
                                    onclick="window.deleteMarker(${marker.id})"
                                    class="flex-1 flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded border border-red-200 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    </svg>
                                    Kustuta
                                </button>
                            </div>
                        </div>
                    `);
                
                markersLayer.value.addLayer(leafletMarker);
            }
        });
        
        // Store markers data globally for popup buttons
        (window as any).markersData = markers;
    } catch (error) {
        console.error('Error loading markers:', error);
    }
};

// Global functions for popup buttons
(window as any).editMarker = (markerId: number) => {
    const markers = (window as any).markersData as Marker[];
    const marker = markers.find(m => m.id === markerId);
    if (marker) {
        editMarker(marker);
    }
};

(window as any).deleteMarker = (markerId: number) => {
    deleteMarker(markerId);
};

onMounted(() => {
    if (!mapEl.value) return;
    
    mapInstance.value = L.map(mapEl.value, {
        zoomControl: true,
    }).setView([58.5953, 25.0136], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(mapInstance.value);

    markersLayer.value = L.layerGroup().addTo(mapInstance.value);

    mapInstance.value.on('click', mapClick);
    
    loadMarkers();
});
</script>

<template>
    <div ref="mapEl" class="map z-10 h-full"></div>

    <Dialog :open="showDialog" @update:open="(open) => { showDialog = open; if (!open) { editingMarker = null; selectedLocation = undefined; formName = ''; formDescription = ''; } }">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ editingMarker ? 'Muuda markerit' : 'Salvesta uus marker' }}</DialogTitle>
                <DialogDescription>{{ editingMarker ? 'Muuda markeri andmeid' : 'Lisa nimi ja kirjeldus' }}</DialogDescription>
            </DialogHeader>
            <form @submit="handleSubmit">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"> 
                        <Label class="mb-1.5" for="name">Nimi</Label>
                        <Input id="name" name="name" v-model="formName" required />
                    </div>

                    <div>
                        <Label class="mb-1.5" for="lat">Lat</Label>
                        <Input id="lat" name="lat" disabled :model-value="selectedLocation?.lat?.toFixed(6)" />
                    </div>

                    <div>
                        <Label class="mb-1.5" for="lng">Lng</Label>
                        <Input id="lng" name="lng" disabled :model-value="selectedLocation?.lng?.toFixed(6)" />
                    </div>
                    
                    <div class="col-span-2">
                        <Label class="mb-1.5" for="description">Kirjeldus</Label>
                        <Textarea id="description" name="description" v-model="formDescription" />
                    </div>
                    
                    <Button type="submit" class="col-span-2">
                        {{ editingMarker ? 'Uuenda' : 'Salvesta' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>