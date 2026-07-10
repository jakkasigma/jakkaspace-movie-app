import './bootstrap';
import './welcome';

import Alpine from 'alpinejs';
import avatarCropper from './avatar-cropper';

window.Alpine = Alpine;

Alpine.data('avatarCropper', avatarCropper);

Alpine.start();
