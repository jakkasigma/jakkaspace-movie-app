import './bootstrap';
import './welcome';

import Alpine from 'alpinejs';
import avatarCropper from './avatar-cropper';
import localizeTimestamps from './time-localizer';

window.Alpine = Alpine;

Alpine.data('avatarCropper', avatarCropper);

Alpine.start();

document.addEventListener('DOMContentLoaded', localizeTimestamps);
window.addEventListener('timestamps-updated', localizeTimestamps);
