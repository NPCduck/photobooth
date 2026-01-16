<script setup>
    import { ref, onMounted } from 'vue';
    import { router } from '@inertiajs/vue3';

    const props = defineProps({ event: Object });

    const step = ref('email'); // krok: 'email' | 'package' | 'capture'
    const email = ref('');
    const packages = ref([]);
    const selectedPackage = ref(null);
    const guestId = ref(null);
    const errorMessage = ref('');
    const uploadMessage = ref('');

    const imageFile = ref(null);
    const previewUrl = ref(null);
    const finalImageUrl = ref(null);
    const cameraStream = ref(null);
    const videoRef = ref(null);

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
                body: JSON.stringify({
                    email: email.value,
                }),
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

    async function confirmPackage() {
        if (!selectedPackage.value) {
            alert('Vyberte balíček!');
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

    function onFileChange(e) {
        const file = e.target.files[0];
        if (file) {
            imageFile.value = file;
            previewUrl.value = URL.createObjectURL(file);
            finalImageUrl.value = previewUrl.value;
        }
    }

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
        
        cameraStream.value = await navigator.mediaDevices.getUserMedia({ video: true });
        videoRef.value.srcObject = cameraStream.value;
        videoRef.value.play();
    }

    function takePhoto() {
        if (!videoRef.value) return;

        const canvas = document.createElement('canvas');
        canvas.width = videoRef.value.videoWidth;
        canvas.height = videoRef.value.videoHeight;
        const ctx = canvas.getContext('2d');

        ctx.drawImage(videoRef.value, 0, 0);

        const overlay = new Image();
        overlay.src = route('private.image', {
            user_id: props.event.user_id,
            event_id: props.event.id,
            path: 'overlays',
            file: 'frame_img',
        });

        overlay.onload = () => {
            ctx.drawImage(overlay, 0, 0, canvas.width, canvas.height);
            finalImageUrl.value = canvas.toDataURL('image/jpeg');
            stopCamera();
        }
    }

    function stopCamera() {
        if (cameraStream.value) {
            cameraStream.value.getTracks().forEach(track => track.stop());
            cameraStream.value = null;
        }
    }

    async function uploadPhoto() {
        if (!guestId.value) return alert('Hosť nebol určený.');

        const file = imageFile.value;
        if (!file && !finalImageUrl.value)
            return alert('Vyberte alebo odfoťte fotku pred nahraním.');

        const formData = new FormData();
        formData.append('guest_id', guestId.value);

        if (file) {
            formData.append('photo', file);
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

        if (!response.ok) {
            const text = await response.text(); // ⚠️ dôležité pri erroroch
            console.error(text);
            uploadMessage.value = 'Nahrávanie fotky sa nepodarilo.';
            return;
        }

        const data = await response.json();
        uploadMessage.value = 'Fotka bola úspešne nahraná.';
        imageFile.value = null;
        finalImageUrl.value = null;
    }

    onMounted(() => {
        // landing image (pred fotením)
        finalImageUrl.value = route('private.image', {
            user_id: props.event.user_id,
            event_id: props.event.id,
            path: 'overlays',
            file: 'landing_img',
        });
    });
</script>


<template>
    <div class="capture-page">
        <!-- Krok 1: Zadaj email -->
        <div v-if="step === 'email'">
            <input v-model="email" type="email" placeholder="Zadajte email">
            <button @click="checkEmail">Pokračovať</button>
            <p v-if="errorMessage" class="text-red-500">{{ errorMessage }}</p>
        </div>

        <!-- Krok 2: Vyber balíček (pre nové emaily) -->
        <div v-if="step === 'package'">
            <h3>Vyberte balíček</h3>
            <div v-for="pkg in packages" :key="pkg.id">
                <input type="radio" :value="pkg.id" v-model="selectedPackage"> {{ pkg.name }} ({{ pkg.photo_limit_person }} fotiek)
            </div>
            <button @click="confirmPackage">Potvrdiť</button>
        </div>

        <!-- Krok 3: Fotenie/nahrávanie fotiek -->
        <div v-if="step === 'capture'">
            <div>
                <input type="file" accept="image/*" @change="onFileChange">
                <button @click="startCamera">Spustiť kameru</button>
                <div v-if="cameraStream">
                    <video ref="videoRef" autoplay></video>
                    <button @click="takePhoto">Odfoť fotku</button>
                    <button @click="stopCamera">Zastaviť kameru</button>
                </div>
                <button @click="uploadPhoto">Odoslať fotku</button>
            </div>
            <p v-if="uploadMessage">{{ uploadMessage }}</p>
        </div>
    </div>
</template>