<script setup>
    import { ref, onMounted } from 'vue';
    import { router } from '@inertiajs/vue3';

    const props = defineProps({
        event: Object,
    });

    const imageFile = ref(null);
    const previewUrl = ref(null);
    const finalImageUrl = ref(null);
    const cameraStream = ref(null);
    const videoRef = ref(null);

    function onFileChange(e) {
        const file = e.target.files[0];
        if (file) {
            imageFile.value = file;
            previewUrl.value = URL.createObejctURL(file);;
        }
    }

    async function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
        
        cameraStream.value = await navigator.mediaDevices.getUserMedia({ video: true});
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
        overlay.src = route('private.image',{
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
        const file = imageFile.value;
        if (!file && !finalImageUrl.value) return alert('Vyberte alebo odfoťte fotku pred nahraním.');

        const formData = new FormData();
        if (file) formData.append('photo', file);
        else {
            const blob = await (await fetch(finalImageUrl.value)).blob();
            formData.append('photo', blob, 'photo.jpg');
        }

        await fetch(route('capture.upload', props.event.public_token), {
            method: 'POST',
            body: formData,
        }).then(res => {
            if (res.ok) alert('Fotka bola úspešne nahraná.');
            else alert('Nahrávanie fotky sa nepodarilo.');
        });
    }

    onMounted(() => {
        finalImageUrl.value = route('private.image', {
            user_id: props.event.user_id,
            event_id: props.event.id,
            path: 'overlays',
            file: 'landing_img',
        });
    });
</script>

<template>
    <div class="bg-sidebarbg min-h-screen p-4 text-center flex flex-col items-center">
        <div>
            <div>
                <h2 class="text-2xl font-bold mb-4 text-white">
                    {{ props.event.name }}
                </h2>
                <div v-if="finalImageUrl">
                    <img :src="finalImageUrl" alt="taken_photo">
                </div>
                <div>
                    <div class="flex">
                        <label for="upload-photo" class="text-white">Nahrať fotku</label>
                        <input id="upload-photo" type="file" accept="image/*" @change="onFileChange()" class="text-white">
                    </div>
                    <div>
                        <button @click="startCamera()" class="p-4 rounded-md bg-highlight text-white my-4">
                            Spustiť kameru
                        </button>
                        <div v-if="cameraStream">
                            <video ref="videoRef" autoplay></video>
                            <button @click="takePhoto()">
                                Odfoť fotku
                            </button>
                            <button @click="stopCamera()">
                                Zastaviť kameru
                            </button>
                            <button @click="uploadPhoto()">
                                Odoslať fotku
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>