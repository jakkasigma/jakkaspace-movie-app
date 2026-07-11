export default function avatarCropper() {
    return {
        loading: false,
        error: null,

        async fileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.error = null;
            this.loading = true;

            try {
                const img = await this.loadImage(file);
                const blob = await this.centerCrop(img, 400, 400);
                await this.upload(blob);
                this.loading = false;
            } catch (err) {
                this.error = err.message;
                this.loading = false;
            }

            event.target.value = '';
        },

        loadImage(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = () => reject(new Error('Gagal memuat gambar'));
                img.src = URL.createObjectURL(file);
            });
        },

        centerCrop(img, targetWidth, targetHeight) {
            const canvas = document.createElement('canvas');
            canvas.width = targetWidth;
            canvas.height = targetHeight;
            const ctx = canvas.getContext('2d');

            const size = Math.min(img.width, img.height);
            const x = (img.width - size) / 2;
            const y = (img.height - size) / 2;

            ctx.drawImage(img, x, y, size, size, 0, 0, targetWidth, targetHeight);

            return new Promise((resolve) => {
                canvas.toBlob((blob) => resolve(blob), 'image/jpeg', 0.8);
            });
        },

        async upload(blob) {
            const formData = new FormData();
            formData.append('avatar', blob, 'avatar.jpg');

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

            const img = this.$refs.avatarPreview;
            if (img) img.src = data.url;

            window.dispatchEvent(new CustomEvent('avatar-updated', { detail: { url: data.url } }));
        },
    };
}
