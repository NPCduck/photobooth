<script setup>
    import { ref, onMounted } from 'vue';

    const props = defineProps({ event: Object });

    /* ====== STATE ====== */
    const step = ref('email'); // email | package | capture
    const email = ref('');
    const packages = ref([]);
    const selectedPackage = ref(null);
    const guestId = ref(null);

    const errorMessage = ref('');
    const uploadMessage = ref('');
    const uploading = ref(false);

    /* ====== IMAGE STATE ====== */
    const imageFile = ref(null);        // vybraný súbor
    const finalImageUrl = ref(null);    // odfotená / vybraná fotka (DATA URL)
    const landingImageUrl = ref(null);  // LEN na zobrazenie

    /* ====== CAMERA ====== */
    const cameraStream = ref(null);
    const videoRef = ref(null);

    /* ====== EMAIL CHECK ====== */
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

    /* ====== CREATE GUEST ====== */
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

    /* ====== FILE SELECT ====== */
    function onFileChange(e) {
        const file = e.target.files[0];
        if (!file) return;

        imageFile.value = file;
        finalImageUrl.value = URL.createObjectURL(file);
    }

    /* ====== CAMERA ====== */
    async function startCamera() {
        if (!navigator.mediaDevices?.getUserMedia) return;

        cameraStream.value = await navigator.mediaDevices.getUserMedia({ video: true });
        videoRef.value.srcObject = cameraStream.value;
        videoRef.value.play();
    }

    function stopCamera() {
        if (!cameraStream.value) return;

        cameraStream.value.getTracks().forEach(track => track.stop());
        cameraStream.value = null;
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
        };
    }

    /* ====== UPLOAD ====== */
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

            // úspech → redirect
            window.location.href = data.redirect;

        } catch (e) {
            console.error(e);
            uploadMessage.value = 'Nahrávanie zlyhalo.';
        } finally {
            uploading.value = false;
        }
    }

    /* ====== INIT ====== */
    onMounted(() => {
        landingImageUrl.value = route('private.image', {
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
            <div class="flex flex-col items-center">
                <img v-if="landingImageUrl" :src="landingImageUrl" alt="Landing Image" class="flex mx-auto justify-center mb-4">
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