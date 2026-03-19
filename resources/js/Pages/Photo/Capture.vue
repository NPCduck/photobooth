<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({ event: Object });

/* ================= STATE ================= */
const step = ref('email');
const email = ref('');
const packages = ref([]);
const selectedPackage = ref(null);
const guestId = ref(null);
const errorMessage = ref('');
const uploadMessage = ref('');
const uploading = ref(false);

/* ================= IMAGE ================= */
const imageFile = ref(null);
const finalImageUrl = ref(null);
const landingImageUrl = ref(null);

/* ================= CAMERA ================= */
const videoRef = ref(null);
const previewCanvas = ref(null);
const cameraStream = ref(null);
let previewLoop = null;

/* ================= SVG FRAME ================= */
const svgPathData = ref(null);
const svgViewBox = ref({ width: 1, height: 1 });

/* ================= OVERLAY ================= */
const overlayImageUrl = ref(null);

/* ================= EMAIL ================= */
async function checkEmail() {
    errorMessage.value = '';

    const response = await fetch(route('capture.checkEmail', props.event.public_token), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ email: email.value })
    });

    const data = await response.json();

    if (!response.ok) {
        errorMessage.value = data.message || 'Nastala chyba';
        return;
    }

    if (!data.exists) {
        packages.value = data.packages;
        step.value = 'package';
        return;
    }

    if (data.allowed) {
        guestId.value = data.guest_id;
        step.value = 'capture';
        return;
    }

    errorMessage.value = data.message;
}

/* ================= CREATE GUEST ================= */
async function confirmPackage() {

    if (!selectedPackage.value) {
        alert('Vyberte balíček.');
        return;
    }

    const response = await fetch(route('capture.createGuest', props.event.public_token), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            email: email.value,
            package_id: selectedPackage.value
        })
    });

    const data = await response.json();

    if (!response.ok) {
        errorMessage.value = data.message || 'Chyba pri vytváraní hosťa';
        return;
    }

    guestId.value = data.guest_id;
    step.value = 'capture';
}

/* ================= FILE ================= */
async function onFileChange(e) {
    const file = e.target.files[0];
    if (!file) return;

    imageFile.value = file;

    const img = new Image();
    img.src = URL.createObjectURL(file);
    await new Promise(res => img.onload = res);

    const blob = await processToBlob({
        source: img,
        width: img.width,
        height: img.height
    });

    finalImageUrl.value = URL.createObjectURL(blob);
}

/* ================= CAMERA ================= */
async function startCamera() {

    if (!navigator.mediaDevices?.getUserMedia) {
        alert('Kamera nie je podporovaná');
        return;
    }

    cameraStream.value = await navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: 'user',
            width: { ideal: 4096 },
            height: { ideal: 4096 }
        },
        audio: false
    });

    await nextTick();

    videoRef.value.srcObject = cameraStream.value;
    await videoRef.value.play();

    startPreview();
}

function stopCamera() {

    if (previewLoop) {
        cancelAnimationFrame(previewLoop);
        previewLoop = null;
    }

    if (!cameraStream.value) return;

    cameraStream.value.getTracks().forEach(track => track.stop());
    cameraStream.value = null;
}

/* ================= LIVE PREVIEW ================= */
function startPreview() {
    const canvas = previewCanvas.value;
    const ctx = canvas.getContext('2d');

    const overlay = new Image();
    overlay.src = overlayImageUrl.value || '';
    overlay.decode().catch(() => {});

    function render() {
        if (!videoRef.value || !svgPathData.value) {
            previewLoop = requestAnimationFrame(render);
            return;
        }

        const videoWidth = videoRef.value.videoWidth;
        const videoHeight = videoRef.value.videoHeight;
        const frameRatio = svgViewBox.value.width / svgViewBox.value.height;
        const videoRatio = videoWidth / videoHeight;

        let cropWidth, cropHeight, offsetX, offsetY;

        if (videoRatio > frameRatio) {
            cropHeight = videoHeight;
            cropWidth = videoHeight * frameRatio;
            offsetX = (videoWidth - cropWidth) / 2;
            offsetY = 0;
        } else {
            cropWidth = videoWidth;
            cropHeight = videoWidth / frameRatio;
            offsetX = 0;
            offsetY = (videoHeight - cropHeight) / 2;
        }

        canvas.width = cropWidth;
        canvas.height = cropHeight;

        const scaleX = cropWidth / svgViewBox.value.width;
        const scaleY = cropHeight / svgViewBox.value.height;

        const clip = new Path2D(svgPathData.value);

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.save();
        ctx.scale(scaleX, scaleY);
        ctx.clip(clip);
        ctx.drawImage(videoRef.value, offsetX, offsetY, cropWidth, cropHeight, 0, 0, svgViewBox.value.width, svgViewBox.value.height);
        ctx.restore();

        if (overlay.src) {
            ctx.save();
            ctx.scale(scaleX, scaleY);
            ctx.clip(clip);
            ctx.drawImage(overlay, 0, 0, svgViewBox.value.width, svgViewBox.value.height);
            ctx.restore();
        }

        previewLoop = requestAnimationFrame(render);
    }

    render();
}

/* ================= CORE PHOTO PROCESS ================= */
async function processPhoto({ source, width, height }) {

    const frameRatio = svgViewBox.value.width / svgViewBox.value.height;
    const sourceRatio = width / height;

    let cropWidth, cropHeight, offsetX, offsetY;

    if (sourceRatio > frameRatio) {
        cropHeight = height;
        cropWidth = height * frameRatio;
        offsetX = (width - cropWidth) / 2;
        offsetY = 0;
    } else {
        cropWidth = width;
        cropHeight = width / frameRatio;
        offsetX = 0;
        offsetY = (height - cropHeight) / 2;
    }

    const canvas = document.createElement('canvas');
    canvas.width = cropWidth;
    canvas.height = cropHeight;

    const ctx = canvas.getContext('2d');

    const scaleX = cropWidth / svgViewBox.value.width;
    const scaleY = cropHeight / svgViewBox.value.height;

    const clip = new Path2D(svgPathData.value);

    // IMAGE / VIDEO
    ctx.save();
    ctx.scale(scaleX, scaleY);
    ctx.clip(clip);
    ctx.drawImage(source, offsetX, offsetY, cropWidth, cropHeight, 0, 0, svgViewBox.value.width, svgViewBox.value.height);
    ctx.restore();

    // OVERLAY
    if (overlayImageUrl.value) {
        const overlay = new Image();
        overlay.src = overlayImageUrl.value;
        await overlay.decode();

        ctx.save();
        ctx.scale(scaleX, scaleY);
        ctx.clip(clip);
        ctx.drawImage(overlay, 0, 0, svgViewBox.value.width, svgViewBox.value.height);
        ctx.restore();
    }

    // EMAIL TEXT
    if (email.value) {
        const pxPerMm = 96 / 25.4;
        const paddingBottom = 3 * pxPerMm;

        const x = canvas.width / 2;
        const y = canvas.height - paddingBottom;

        const fontSize = Math.floor(canvas.height * 0.035);
        ctx.font = `${fontSize}px Arial`;
        ctx.fillStyle = 'white';
        ctx.textAlign = 'center';

        ctx.shadowColor = 'rgba(0,0,0,0.7)';
        ctx.shadowBlur = 4;

        ctx.strokeStyle = 'black';
        ctx.lineWidth = fontSize * 0.15;
        ctx.strokeText(
            email.value,
            x, y,
        );

        ctx.fillText(
            email.value,
            x, y,
        );

        ctx.shadowBlur = 0;
    }

    return canvas;
}

/* ================= EXPORT HELPER ================= */
async function processToBlob({ source, width, height }) {
    const canvas = await processPhoto({ source, width, height });

    return new Promise(res =>
        canvas.toBlob(blob => res(blob), 'image/png', 1)
    );
}

/* ================= PHOTO CAPTURE ================= */
async function takePhoto() {

    const blob = await processToBlob({
        source: videoRef.value,
        width: videoRef.value.videoWidth,
        height: videoRef.value.videoHeight
    });

    finalImageUrl.value = URL.createObjectURL(blob);

    stopCamera();
}

/* ================= UPLOAD ================= */
async function uploadPhoto() {

    if (uploading.value) return;

    if (!guestId.value) {
        alert('Hosť nebol určený');
        return;
    }

    if (!imageFile.value && !finalImageUrl.value) {
        alert('Najskôr odfoť alebo vyber fotku');
        return;
    }

    uploading.value = true;

    const formData = new FormData();
    formData.append('guest_id', guestId.value);

    if (imageFile.value) {

        const img = new Image();
        img.src = URL.createObjectURL(imageFile.value);
        await new Promise(res => img.onload = res);

        const blob = await processToBlob({
            source: img,
            width: img.width,
            height: img.height
        });

        formData.append('photo', blob, 'photo.png');

    } else {

        const blob = await (await fetch(finalImageUrl.value)).blob();
        formData.append('photo', blob, 'photo.png');
    }

    const response = await fetch(route('capture.upload', props.event.public_token), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
        uploadMessage.value = data.message || 'Upload zlyhal';
    } else {
        window.location.href = data.redirect;
    }

    uploading.value = false;
}

/* ================= INIT ================= */
onMounted(() => {

    landingImageUrl.value = route('private.image', {
        user_id: props.event.user_id,
        event_id: props.event.id,
        path: 'overlays',
        file: 'landing_img'
    });

    overlayImageUrl.value = route('private.image', {
        user_id: props.event.user_id,
        event_id: props.event.id,
        path: 'overlays',
        file: 'frame_img'
    });

    const svgUrl = route('private.frameSvg', {
        user_id: props.event.user_id,
        event_id: props.event.id,
        path: 'overlays',
        file: 'frame.svg'
    });

    fetch(svgUrl)
        .then(res => res.text())
        .then(svg => {

            const parser = new DOMParser();
            const doc = parser.parseFromString(svg, 'image/svg+xml');

            const svgEl = doc.querySelector('svg');
            const path = doc.querySelector('path');

            const viewBox = svgEl.getAttribute('viewBox').split(' ');

            svgViewBox.value.width = parseFloat(viewBox[2]);
            svgViewBox.value.height = parseFloat(viewBox[3]);

            svgPathData.value = path.getAttribute('d');
        });
});
</script>

<template>
<div class="min-h-screen flex flex-col items-center p-4 bg-gray-100">
    <!-- EMAIL -->
    <div v-if="step === 'email'" class="w-full max-w-sm space-y-4">
        <input v-model="email" type="email" class="w-full p-3 border rounded" placeholder="Zadajte email" />
        <button @click="checkEmail" class="w-full bg-black text-white py-3 rounded">Pokračovať</button>
        <p v-if="errorMessage" class="text-red-500 text-center">{{ errorMessage }}</p>
    </div>

    <!-- PACKAGE -->
    <div v-if="step === 'package'" class="w-full max-w-sm space-y-4">
        <h3 class="text-xl font-semibold text-center">Vyberte balíček</h3>
        <label v-for="pkg in packages" :key="pkg.id" class="block border p-3 rounded">
            <input type="radio" v-model="selectedPackage" :value="pkg.id" />
            {{ pkg.name }} ({{ pkg.photo_limit_person == 0 ? 'neobmedzený počet' : pkg.photo_limit_person }} fotiek)
        </label>
        <button @click="confirmPackage" class="w-full bg-black text-white py-3 rounded">Potvrdiť</button>
    </div>

    <!-- CAPTURE -->
    <div v-if="step === 'capture'" class="w-full max-w-md space-y-4">
        <img v-if="landingImageUrl" :src="landingImageUrl" class="rounded shadow mx-auto" />
        <input type="file" accept="image/*" @change="onFileChange" />
        <button @click="startCamera" class="w-full bg-blue-600 text-white py-2 rounded">Spustiť kameru</button>

        <!-- CAMERA -->
        <div
            v-if="cameraStream"
            class="relative w-full overflow-hidden"
            :style="{ aspectRatio: svgViewBox.width + ' / ' + svgViewBox.height }"
        >

            <!-- SVG FRAME + MASK -->
            <div
                v-if="cameraStream"
                class="relative w-full overflow-hidden"
                :style="{ aspectRatio: svgViewBox.width + ' / ' + svgViewBox.height }"
            >

                <video
                    ref="videoRef"
                    autoplay
                    playsinline
                    muted
                    class="hidden">
                </video>

                <canvas
                    ref="previewCanvas"
                    class="absolute inset-0 w-full h-full">
                </canvas>
            </div>

        </div>

        <button @click="takePhoto" class="w-full bg-green-600 text-white py-2 rounded">Odfoť</button>
        <button @click="stopCamera" class="w-full bg-gray-500 text-white py-2 rounded">Zastaviť kameru</button>

        <img v-if="finalImageUrl" :src="finalImageUrl" class="rounded shadow mx-auto" />
        <button @click="uploadPhoto" class="w-full bg-black text-white py-3 rounded">Odoslať fotku</button>
        <p v-if="uploadMessage" class="text-center">{{ uploadMessage }}</p>
    </div>
</div>
</template>