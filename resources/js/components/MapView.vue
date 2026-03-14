<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';

const mapEl = ref(null);
const mapClick = (e: PointerEvent) => {
    selectedLocation.value = { lat: e.latlng.lat, lng: e.latlng.lng };
};

const selectedLocation = ref<{ lat: number; lng: number }>();

onMounted(() => {
    const map = L.map(mapEl.value, {
        zoomControl: true,
    }).setView([59.4, 24.7], 8);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    map.on('click', mapClick);
});
</script>

<template>
    <div ref="mapEl" class="map z-10 h-full"></div>

    <Dialog :open="!!selectedLocation" @update:open="selectedLocation = undefined">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Salvesta uus marker</DialogTitle>
                <DialogDescription> Lisa nimi ja kirjeldus </DialogDescription>
                <Form action="/" method="post">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2"> 
                            <Label class="mb-1.5" for="name">Nimi</Label>
                            <Input name="name"> </Input>
                        </div>

                        <div>
                            <Label class="mb-1.5" for="lat">Lat</Label>
                            <Input id="lat" name="lat" disabled :default-value="selectedLocation?.lat" />
                        </div>

                        <div>
                            <Label class="mb-1.5" for="lng">Lng</Label>
                            <Input id="lng" name="lng" disabled :default-value="selectedLocation?.lng" />
                        </div>
                        <Textarea name="description" class="col-span-2" />
                        <Button type="submit" class="col-span-2">Salvesta</Button>
                    </div>
                </Form>
            </DialogHeader>
        </DialogContent>
    </Dialog>
</template>
