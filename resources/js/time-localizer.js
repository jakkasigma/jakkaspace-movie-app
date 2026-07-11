export default function localizeTimestamps() {
    document.querySelectorAll('[data-utc]').forEach((el) => {
        const utc = new Date(el.getAttribute('data-utc') + 'Z');
        if (isNaN(utc.getTime())) return;

        const fmt = el.dataset.fmt || 'time';

        switch (fmt) {
            case 'time':
                el.textContent = utc.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                break;
            case 'date':
                el.textContent = utc.toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' });
                break;
            case 'date-sep':
                el.textContent = formatDateSep(utc);
                break;
            case 'fulldate':
                el.textContent = utc.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                break;
            case 'diff':
                el.textContent = relativeTime(utc);
                break;
            default:
                el.textContent = utc.toLocaleString();
        }
    });
}

function formatDateSep(date) {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    const d = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    if (d.getTime() === today.getTime()) return 'Hari Ini';
    if (d.getTime() === yesterday.getTime()) return 'Kemarin';
    return date.toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' });
}

function relativeTime(date) {
    const diff = Date.now() - date.getTime();
    const mins = Math.floor(diff / 60000);

    if (mins < 1) return 'baru saja';
    if (mins < 60) return `${mins} menit lalu`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} hari lalu`;
    return date.toLocaleDateString([], { day: 'numeric', month: 'short' });
}
