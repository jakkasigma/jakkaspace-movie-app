import Cropper from 'cropperjs';

export default function avatarCropper() {
    return {
        cropper: null,
        imageUrl: null,
        showModal: false,
        loading: false,
        error: null,

        fileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.error = null;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.imageUrl = e.target.result;
                this.showModal = true;
                this.$nextTick(() => this.initCropper());
            };
            reader.readAsDataURL(file);

            event.target.value = '';
        },

        initCropper() {
            if (this.cropper) {
                this.cropper.destroy();
            }
            this.cropper = new Cropper(this.$refs.cropImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                scalable: false,
                zoomable: true,
                cropBoxMovable: true,
                cropBoxResizable: false,
                minContainerWidth: 300,
                minContainerHeight: 300,
            });
        },

        zoomIn() {
            this.cropper?.zoom(0.1);
        },

        zoomOut() {
            this.cropper?.zoom(-0.1);
        },

        reset() {
            this.cropper?.reset();
        },

        cancel() {
            this.showModal = false;
            this.imageUrl = null;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },

        async save() {
            if (!this.cropper) return;

            this.loading = true;
            this.error = null;

            const canvas = this.cropper.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingQuality: 'high',
            });

            const blob = await new Promise((resolve) => {
                canvas.toBlob((b) => resolve(b), 'image/jpeg', 0.8);
            });

            if (!blob) {
                this.error = 'Gagal memproses foto. Coba lagi.';
                this.loading = false;
                return;
            }

            const formData = new FormData();
            formData.append('avatar', blob, 'avatar.jpg');

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('/profile/avatar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw new Error(data.message || data.avatar?.[0] || 'Upload gagal');
                }

                const data = await response.json();
                this.cancel();

                const img = this.$refs.avatarPreview;
                if (img) img.src = data.url;

                window.dispatchEvent(new CustomEvent('avatar-updated', { detail: { url: data.url } }));
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        },
    };
}
