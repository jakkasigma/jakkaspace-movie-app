const hamburger = document.querySelector('[data-menu-button]');
const navCenter = document.querySelector('[data-menu-panel]');
const navLinks = document.querySelectorAll('[data-menu-link]');
const clock = document.getElementById('clock');
const preSplash = document.getElementById('pre-splash');
const splashStart = document.getElementById('splash-start');
const introOverlay = document.getElementById('intro-overlay');
const introSound = document.getElementById('intro-sound');

function finishIntro() {
    document.body.classList.add('intro-complete');
    introOverlay?.setAttribute('hidden', 'hidden');
}

function startIntro() {
    preSplash?.classList.add('hidden');
    document.body.classList.add('anim-started');

    introSound?.play().catch(() => {
        // Browser autoplay rules can still block sound; the visual intro should continue.
    });

    window.setTimeout(finishIntro, 4600);
}

splashStart?.addEventListener('click', startIntro);

if (! splashStart) {
    startIntro();
}

hamburger?.addEventListener('click', () => {
    navCenter?.classList.toggle('open');
});

navLinks.forEach((link) => {
    link.addEventListener('click', () => {
        navCenter?.classList.remove('open');
    });
});

function updateClock() {
    if (! clock) {
        return;
    }

    const time = new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    }).format(new Date());

    clock.textContent = `YOGYAKARTA - ${time}`;
}

updateClock();
setInterval(updateClock, 1000 * 30);
