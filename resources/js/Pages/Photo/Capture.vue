<script setup>
import { ref, onMounted, nextTick } from 'vue';

const props = defineProps({ event: Object });

/* ================= STATE ================= */
const step = ref('email'); // email | package | capture
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

/* ================= OVERLAY SETTINGS ================= */
const overlaySettings = ref({
    frame_position: 'center',
    frame_stretch: true
});
const overlayImageUrl = ref(null);

/* ================= CAMERA ================= */
const cameraStream = ref(null);
const videoRef = ref(null);

/* ================= EMAIL ================= */
async function checkEmail() {
    errorMessage.value = '';

    const response = await fetch(
        route('capture.checkEmail', props.event.public_token),
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({ email: email.value }),
        }
    );

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

    const response = await fetch(
        route('capture.createGuest', props.event.public_token),
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({
                email: email.value,
                package_id: selectedPackage.value,
            }),
        }
    );

    const data = await response.json();

    if (!response.ok) {
        errorMessage.value = data.message || 'Chyba pri vytváraní hosťa';
        return;
    }

    guestId.value = data.guest_id;
    step.value = 'capture';
}

/* ================= FILE ================= */
function onFileChange(e) {
    const file = e.target.files[0];
    if (!file) return;

    imageFile.value = file;
    finalImageUrl.value = URL.createObjectURL(file);
}

/* ================= CAMERA ================= */
async function startCamera() {
    if (!navigator.mediaDevices?.getUserMedia) {
        alert('Kamera nie je podporovaná');
        return;
    }

    cameraStream.value = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user' },
        audio: false,
    });

    await nextTick();

    if (!videoRef.value) return;

    videoRef.value.srcObject = cameraStream.value;
    await videoRef.value.play();
}

function stopCamera() {
    if (!cameraStream.value) return;

    cameraStream.value.getTracks().forEach(track => track.stop());
    cameraStream.value = null;
}

function takePhoto() {
    if (!videoRef.value) return;

    // Vytvor štvorcový canvas
    const size = Math.min(videoRef.value.videoWidth, videoRef.value.videoHeight);
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;

    const ctx = canvas.getContext('2d');
    
    // Nakresli video centované na štvorcový canvas
    const offsetX = (videoRef.value.videoWidth - size) / 2;
    const offsetY = (videoRef.value.videoHeight - size) / 2;
    
    ctx.drawImage(
        videoRef.value,
        offsetX, offsetY, size, size,
        0, 0, size, size
    );

    // Aplikuj overlay do fotky
    if (overlayImageUrl.value) {
        const overlay = new Image();
        overlay.src = overlayImageUrl.value;
        overlay.crossOrigin = 'anonymous';

        overlay.onload = () => {
            if (overlaySettings.value.frame_stretch) {
                ctx.drawImage(overlay, 0, 0, size, size);
            } else {
                const overlayWidth = overlay.width;
                const overlayHeight = overlay.height;
                let x = 0, y = 0;

                switch (overlaySettings.value.frame_position) {
                    case 'top-left':
                        x = 0;
                        y = 0;
                        break;
                    case 'top-right':
                        x = size - overlayWidth;
                        y = 0;
                        break;
                    case 'bottom-left':
                        x = 0;
                        y = size - overlayHeight;
                        break;
                    case 'bottom-right':
                        x = size - overlayWidth;
                        y = size - overlayHeight;
                        break;
                    case 'center':
                        x = (size - overlayWidth) / 2;
                        y = (size - overlayHeight) / 2;
                        break;
                }
                ctx.drawImage(overlay, x, y);
            }
            finishPhoto(canvas);
        };
        overlay.onerror = () => {
            finishPhoto(canvas);
        };
    } else {
        finishPhoto(canvas);
    }
}

function finishPhoto(canvas) {
    finalImageUrl.value = canvas.toDataURL('image/jpeg', 0.9);
    stopCamera();
}

function getOverlayStyle() {
    if (overlaySettings.value.frame_stretch) {
        return {
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            objectFit: 'cover'
        };
    }

    // Pre fixné rozlíšenia - aspoň nejaký CSS fallback
    const positions = {
        'top-left': { top: 0, left: 0 },
        'top-right': { top: 0, right: 0 },
        'bottom-left': { bottom: 0, left: 0 },
        'bottom-right': { bottom: 0, right: 0 },
        'center': { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }
    };

    return {
        ...positions[overlaySettings.value.frame_position] || positions.center,
        objectFit: 'contain'
    };
}

/* ================= UPLOAD ================= */
async function uploadPhoto() {
    if (uploading.value) return;
    if (!guestId.value) return alert('Hosť nebol určený.');

    if (!imageFile.value && !finalImageUrl.value) {
        alert('Najskôr odfoť alebo vyber fotku.');
        return;
    }

    uploading.value = true;
    uploadMessage.value = '';

    try {
        const formData = new FormData();
        formData.append('guest_id', guestId.value);

        if (imageFile.value) {
            formData.append('photo', imageFile.value);
        } else {
            const blob = await (await fetch(finalImageUrl.value)).blob();
            formData.append('photo', blob, 'photo.jpg');
        }

        const response = await fetch(
            route('capture.upload', props.event.public_token),
            {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute('content'),
                },
                body: formData,
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            uploadMessage.value = data.message || 'Nahrávanie zlyhalo.';
            return;
        }

        window.location.href = data.redirect;
    } catch (e) {
        console.error(e);
        uploadMessage.value = 'Nahrávanie zlyhalo.';
    } finally {
        uploading.value = false;
    }
}

/* ================= INIT ================= */
onMounted(() => {
    landingImageUrl.value = route('private.image', {
        user_id: props.event.user_id,
        event_id: props.event.id,
        path: 'overlays',
        file: 'landing_img',
    });

    overlayImageUrl.value = route('private.image', {
        user_id: props.event.user_id,
        event_id: props.event.id,
        path: 'overlays',
        file: 'frame_img',
    });

    // Načítaj overlay nastavenia
    if (props.event.overlays) {
        overlaySettings.value.frame_position = props.event.overlays.frame_position || 'center';
        overlaySettings.value.frame_stretch = props.event.overlays.frame_stretch !== false;
    }
});
</script>

<template>
    <div class="min-h-screen flex flex-col items-center p-4 bg-gray-100">
        <!-- EMAIL -->
        <div v-if="step === 'email'" class="w-full max-w-sm space-y-4">
            <input v-model="email" type="email" class="w-full p-3 rounded border" placeholder="Zadajte email">
            <button @click="checkEmail" class="w-full bg-black text-white py-3 rounded">
                Pokračovať
            </button>
            <p v-if="errorMessage" class="text-red-500 text-center">{{ errorMessage }}</p>
        </div>

        <!-- PACKAGE -->
        <div v-if="step === 'package'" class="w-full max-w-sm space-y-4">
            <h3 class="text-xl font-semibold text-center">Vyberte balíček</h3>
            <label v-for="pkg in packages" :key="pkg.id" class="block border p-3 rounded">
                <input type="radio" :value="pkg.id" v-model="selectedPackage">
                {{ pkg.name }} ({{ pkg.photo_limit_person }} fotiek)
            </label>
            <button @click="confirmPackage" class="w-full bg-black text-white py-3 rounded">
                Potvrdiť
            </button>
        </div>

        <!-- CAPTURE -->
        <div v-if="step === 'capture'" class="w-full max-w-md space-y-4">
            <img v-if="landingImageUrl" :src="landingImageUrl" class="rounded shadow mx-auto">

            <input type="file" accept="image/*" @change="onFileChange">

            <button @click="startCamera" class="w-full bg-blue-600 text-white py-2 rounded">
                Spustiť kameru
            </button>

            <div v-if="cameraStream" class="space-y-2">
                <div class="relative w-full max-w-sm aspect-square bg-black rounded overflow-hidden mx-auto">
                    <video
                        ref="videoRef"
                        autoplay
                        playsinline
                        muted
                        class="w-full h-full object-cover"
                    />
                    <img
                        v-if="overlayImageUrl"
                        :src="overlayImageUrl"
                        :style="getOverlayStyle()"
                        class="absolute"
                    />
                </div>
                <button @click="takePhoto" class="w-full bg-green-600 text-white py-2 rounded">
                    Odfoť
                </button>
                <button @click="stopCamera" class="w-full bg-gray-500 text-white py-2 rounded">
                    Zastaviť kameru
                </button>
            </div>

            <img v-if="finalImageUrl" :src="finalImageUrl" class="rounded shadow mx-auto">

            <button @click="uploadPhoto" class="w-full bg-black text-white py-3 rounded">
                Odoslať fotku
            </button>

            <p v-if="uploadMessage" class="text-center">{{ uploadMessage }}</p>
        </div>
    </div>
</template>
