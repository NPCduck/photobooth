<script setup>
    import { defineProps, ref, computed, onMounted, watch } from 'vue';
    import { jsPDF } from 'jspdf';
    import html2canvas from 'html2canvas';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

    const props = defineProps({
        event: Object,
    });

    /* ================= PAPER SIZES (mm) ================= */
    const paperSizes = {
        'A4': { width: 210, height: 297, label: 'A4 (210×297 mm)' },
        'A3': { width: 297, height: 420, label: 'A3 (297×420 mm)' },
        'A5': { width: 148, height: 210, label: 'A5 (148×210 mm)' },
        'A6': { width: 105, height: 148, label: 'A6 (105×148 mm)' },
        '10x15': { width: 100, height: 150, label: '10×15 cm' },
        '13x18': { width: 130, height: 180, label: '13×18 cm' },
    };

    /* ================= STATE ================= */
    const selectedPaperSize = ref('A4');
    const photoWidth = ref(80); // mm
    const photoHeight = ref(80); // mm
    const paperMargin = ref(10); // mm (margin na všetkých stranách)
    const photoGap = ref(5); // mm (vzdialenosť medzi fotkami)
    const DPI = 96; // Screen DPI
    const mmToPx = (mm) => (mm * DPI) / 25.4; // Convert mm to pixels

    /* ================= COMPUTED ================= */
    const currentPaperSize = computed(() => paperSizes[selectedPaperSize.value]);
    
    const availableWidth = computed(() => 
        currentPaperSize.value.width - (paperMargin.value * 2)
    );
    
    const availableHeight = computed(() => 
        currentPaperSize.value.height - (paperMargin.value * 2)
    );

    const photosPerRow = computed(() =>
        Math.floor((availableWidth.value + photoGap.value) / (photoWidth.value + photoGap.value))
    );

    const photosPerColumn = computed(() =>
        Math.floor((availableHeight.value + photoGap.value) / (photoHeight.value + photoGap.value))
    );

    const totalPhotosPerPage = computed(() =>
        photosPerRow.value * photosPerColumn.value
    );

    const totalPages = computed(() =>
        Math.ceil((props.event.photos?.length || 0) / totalPhotosPerPage.value)
    );

    const pagesArray = computed(() => {
        const photos = props.event.photos || [];
        const pages = [];
        for (let i = 0; i < totalPages.value; i++) {
            pages.push(photos.slice(
                i * totalPhotosPerPage.value,
                (i + 1) * totalPhotosPerPage.value
            ));
        }
        return pages;
    });

    /* ================= CANVAS RENDERING ================= */
    function renderAllPages() {
        setTimeout(() => {
            for (let i = 0; i < totalPages.value; i++) {
                const canvas = document.getElementById(`page-${i}`);
                if (canvas) {
                    renderPage(i, canvas);
                }
            }
        }, 100);
    }

    function renderPage(pageIndex, canvasElement) {
        const canvas = canvasElement;
        if (!canvas) return;

        const width = mmToPx(currentPaperSize.value.width);
        const height = mmToPx(currentPaperSize.value.height);
        
        canvas.width = width;
        canvas.height = height;
        
        const ctx = canvas.getContext('2d');
        
        // White background
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, width, height);
        
        // Grid of photos
        const marginPx = mmToPx(paperMargin.value);
        const photoWidthPx = mmToPx(photoWidth.value);
        const photoHeightPx = mmToPx(photoHeight.value);
        const gapPx = mmToPx(photoGap.value);
        
        const page = pagesArray.value[pageIndex];
        if (!page) return;
        
        let loadedImages = 0;
        const totalImages = page.length;
        
        page.forEach((photo, index) => {
            const row = Math.floor(index / photosPerRow.value);
            const col = index % photosPerRow.value;
            
            const x = marginPx + col * (photoWidthPx + gapPx);
            const y = marginPx + row * (photoHeightPx + gapPx);
            
            // Draw placeholder border
            ctx.strokeStyle = '#cccccc';
            ctx.lineWidth = 1;
            ctx.strokeRect(x, y, photoWidthPx, photoHeightPx);
            
            // Load and draw image
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = getPhotoUrl(photo);
            
            img.onload = () => {
                ctx.drawImage(img, x, y, photoWidthPx, photoHeightPx);
                loadedImages++;
            };

            img.onerror = () => {
                loadedImages++;
            };
        });
    }

    /* ================= WATCHERS ================= */
    watch(
        [selectedPaperSize, photoWidth, photoHeight, paperMargin, photoGap],
        () => {
            renderAllPages();
        }
    );

    /* ================= EXPORT FUNCTIONS ================= */
    async function exportImage(format) {
        try {
            for (let i = 0; i < totalPages.value; i++) {
                const canvas = document.getElementById(`page-${i}`);
                if (canvas) {
                    const link = document.createElement('a');
                    link.href = canvas.toDataURL(`image/${format === 'jpg' ? 'jpeg' : format}`);
                    link.download = `${props.event.name}_page_${i + 1}.${format}`;
                    link.click();
                    
                    // Malá časová zmena aby bol čas na stiahnutie
                    if (i < totalPages.value - 1) {
                        await new Promise(resolve => setTimeout(resolve, 300));
                    }
                }
            }
        } catch (error) {
            console.error('Export error:', error);
            alert('Chyba pri exporte obrázka.');
        }
    }

    async function exportPDF() {
        try {
            const paperWidth = currentPaperSize.value.width;
            const paperHeight = currentPaperSize.value.height;
            const pdf = new jsPDF({
                orientation: paperWidth > paperHeight ? 'l' : 'p',
                unit: 'mm',
                format: [paperWidth, paperHeight]
            });
            
            for (let i = 0; i < totalPages.value; i++) {
                if (i > 0) pdf.addPage();
                
                const canvas = document.getElementById(`page-${i}`);
                if (canvas) {
                    const imgData = canvas.toDataURL('image/png');
                    pdf.addImage(
                        imgData,
                        'PNG',
                        0,
                        0,
                        paperWidth,
                        paperHeight
                    );
                }
            }
            
            pdf.save(`${props.event.name}_tisk.pdf`);
        } catch (error) {
            console.error('PDF export error:', error);
            alert('Chyba pri exporte PDF.');
        }
    }

    function getPhotoUrl(photo) {
        return route('private.getPhotoUrl', {
            path: photo.path,
        });
    }

    /* ================= LIFECYCLE ================= */
    onMounted(() => {
        renderAllPages();
    });

</script>
<template>
    <AuthenticatedLayout>
        <template #header>
           <div class="flex flex-row justify-between mb-4">
                <h2 class="text-3xl font-normal leading-tight text-gray-800">
                    {{ props.event.name }}
                </h2>
            </div>
        </template>
        <template #default>
            <div class="flex flex-col gap-4 w-full">
                <!-- Container - Šablóna a Nastavenia -->
                <div class="flex flex-col bg-white p-6 shadow rounded-md gap-6">
                    <p class="font-thin text-[25px]">Nastavenia tlače</p>
                    
                    <!-- Nastavenia -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Papier -->
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-sm">Formát papiera</label>
                            <select v-model="selectedPaperSize" class="border rounded p-2 focus:outline-none focus:ring-2 focus:ring-black">
                                <option v-for="(size, key) in paperSizes" :key="key" :value="key">
                                    {{ size.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Šírka fotky -->
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-sm">Šírka fotky (mm)</label>
                            <input v-model.number="photoWidth" type="number" class="border rounded p-2 focus:outline-none focus:ring-2 focus:ring-black" min="10" max="200">
                        </div>

                        <!-- Výška fotky -->
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-sm">Výška fotky (mm)</label>
                            <input v-model.number="photoHeight" type="number" class="border rounded p-2 focus:outline-none focus:ring-2 focus:ring-black" min="10" max="200">
                        </div>

                        <!-- Okraj papiera -->
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-sm">Okraj papiera (mm)</label>
                            <input v-model.number="paperMargin" type="number" class="border rounded p-2 focus:outline-none focus:ring-2 focus:ring-black" min="0" max="50">
                        </div>

                        <!-- Vzdialenosť medzi fotkami -->
                        <div class="flex flex-col gap-2">
                            <label class="font-semibold text-sm">Medzera (mm)</label>
                            <input v-model.number="photoGap" type="number" class="border rounded p-2 focus:outline-none focus:ring-2 focus:ring-black" min="0" max="20">
                        </div>

                        <!-- Info -->
                        <div class="flex flex-col gap-2 bg-gray-50 p-3 rounded">
                            <p class="text-xs text-gray-600">
                                <strong>Na stranu:</strong> {{ totalPhotosPerPage }} fotiek ({{ photosPerRow }}×{{ photosPerColumn }})
                            </p>
                            <p class="text-xs text-gray-600">
                                <strong>Celkom strán:</strong> {{ totalPages }}
                            </p>
                        </div>
                    </div>

                    <!-- Náhľad -->
                    <div class="border-t pt-6">
                        <p class="font-semibold text-sm mb-4">Náhľad</p>
                        <div id="preview-container" class="flex flex-col gap-6 bg-gray-50 p-4 rounded max-h-[600px] overflow-y-auto">
                            <div 
                                v-for="(page, pageIndex) in pagesArray" 
                                :key="pageIndex"
                                class="flex justify-center"
                            >
                                <canvas 
                                    :id="`page-${pageIndex}`"
                                    class="border shadow bg-white"
                                    :style="{ 
                                        maxWidth: '100%',
                                        height: 'auto'
                                    }"
                                ></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tlačidlá na export -->
                    <div class="flex gap-2 flex-wrap border-t pt-4">
                        <button 
                            @click="exportImage('jpg')"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                        >
                            Exportovať ako JPG
                        </button>
                        <button 
                            @click="exportImage('png')"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
                        >
                            Exportovať ako PNG
                        </button>
                        <button 
                            @click="exportPDF()"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition"
                        >
                            Exportovať ako PDF
                        </button>
                    </div>
                </div>

                <!-- Container - Zoznam fotiek -->
                <div class="flex flex-col bg-white p-4 shadow rounded-md gap-4">
                    <p class="font-thin text-[25px]">Fotky ({{ props.event.photos?.length || 0 }})</p>
                    <div class="bg-gray-50 flex rounded-md">
                        <div class="bg-gray-50 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 rounded-md w-full">
                            <div v-for="photo in props.event.photos" :key="photo.id" class="p-2 rounded-md flex flex-col items-center bg-white shadow">
                                <div class="w-full h-48 overflow-hidden rounded-md">
                                    <img :src="getPhotoUrl(photo)" alt="Fotka" class="w-full aspect-square h-full object-cover">
                                </div>
                                <p class="mt-2 text-sm text-gray-700">{{ photo.guest?.email || 'Neznámy' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>