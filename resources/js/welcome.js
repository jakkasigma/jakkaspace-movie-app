const hamburger = document.querySelector('[data-menu-button]');
const navMobilePanel = document.querySelector('[data-menu-panel]');
const navLinks = document.querySelectorAll('[data-menu-link]');
const clock = document.getElementById('clock');
const preSplash = document.getElementById('pre-splash');
const splashStart = document.getElementById('splash-start');
const introOverlay = document.getElementById('intro-overlay');
const introSound = document.getElementById('intro-sound');
const introStorageKey = 'jakkaspace:intro-played';
const hasIntroScreen = Boolean(preSplash && splashStart && introOverlay);

function hasPlayedIntro() {
    try {
        return window.sessionStorage.getItem(introStorageKey) === 'true';
    } catch {
        return false;
    }
}

function rememberIntro() {
    try {
        window.sessionStorage.setItem(introStorageKey, 'true');
    } catch {
        // Storage can be unavailable in private or restricted browser modes.
    }
}

function skipIntro() {
    rememberIntro();
    preSplash?.classList.add('hidden');
    document.body.classList.add('anim-started', 'intro-complete');
    introOverlay?.setAttribute('hidden', 'hidden');
}

function finishIntro() {
    rememberIntro();
    document.body.classList.add('intro-complete');
    introOverlay?.setAttribute('hidden', 'hidden');
}

function startIntro() {
    rememberIntro();
    preSplash?.classList.add('hidden');
    document.body.classList.add('anim-started');

    introSound?.play().catch(() => {
        // Browser autoplay rules can still block sound; the visual intro should continue.
    });

    window.setTimeout(finishIntro, 4600);
}

if (hasPlayedIntro() || ! hasIntroScreen) {
    skipIntro();
} else {
    splashStart.addEventListener('click', startIntro);
}

hamburger?.addEventListener('click', () => {
    navMobilePanel?.classList.toggle('open');
    document.body.classList.toggle('nav-panel-open');
});

navLinks.forEach((link) => {
    link.addEventListener('click', () => {
        navMobilePanel?.classList.remove('open');
        document.body.classList.remove('nav-panel-open');
    });
});

// Close panel when clicking backdrop (outside panel)
document.addEventListener('click', (event) => {
    if (
        navMobilePanel?.classList.contains('open') &&
        ! navMobilePanel.contains(event.target) &&
        ! hamburger?.contains(event.target)
    ) {
        navMobilePanel.classList.remove('open');
        document.body.classList.remove('nav-panel-open');
    }
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

const storyButton = document.querySelector('[data-share-story]');
const storyModal = document.querySelector('[data-story-modal]');
const storyCanvas = document.querySelector('[data-story-canvas]');
const storyCloseButtons = document.querySelectorAll('[data-story-close]');
const storyDownloadButton = document.querySelector('[data-story-download]');
const storyNativeButton = document.querySelector('[data-story-native]');
const storyStatus = document.querySelector('[data-story-status]');

function setStoryStatus(message) {
    if (! storyStatus) {
        return;
    }

    storyStatus.textContent = message;
}

function setStoryBusy(isBusy) {
    [storyDownloadButton, storyNativeButton].forEach((button) => {
        if (button) {
            button.disabled = isBusy;
        }
    });
}

function storySlug(title) {
    return (title || 'jakka-space-story')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '')
        .slice(0, 48) || 'jakka-space-story';
}

function loadStoryImage(source) {
    return new Promise((resolve) => {
        if (! source) {
            resolve(null);

            return;
        }

        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = () => resolve(null);
        image.src = source;
    });
}

function roundedPath(context, x, y, width, height, radius) {
    const safeRadius = Math.min(radius, width / 2, height / 2);

    context.beginPath();
    context.moveTo(x + safeRadius, y);
    context.lineTo(x + width - safeRadius, y);
    context.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
    context.lineTo(x + width, y + height - safeRadius);
    context.quadraticCurveTo(x + width, y + height, x + width - safeRadius, y + height);
    context.lineTo(x + safeRadius, y + height);
    context.quadraticCurveTo(x, y + height, x, y + height - safeRadius);
    context.lineTo(x, y + safeRadius);
    context.quadraticCurveTo(x, y, x + safeRadius, y);
    context.closePath();
}

function drawCoverImage(context, image, x, y, width, height) {
    const scale = Math.max(width / image.width, height / image.height);
    const drawWidth = image.width * scale;
    const drawHeight = image.height * scale;
    const drawX = x + (width - drawWidth) / 2;
    const drawY = y + (height - drawHeight) / 2;

    context.drawImage(image, drawX, drawY, drawWidth, drawHeight);
}

function drawRoundedImage(context, image, x, y, width, height, radius) {
    context.save();
    roundedPath(context, x, y, width, height, radius);
    context.clip();
    drawCoverImage(context, image, x, y, width, height);
    context.restore();
}

function drawWrappedText(context, text, x, y, maxWidth, lineHeight, maxLines) {
    const words = String(text || '').split(/\s+/).filter(Boolean);
    const lines = [];
    let currentLine = '';

    words.forEach((word) => {
        const testLine = currentLine ? `${currentLine} ${word}` : word;

        if (context.measureText(testLine).width <= maxWidth) {
            currentLine = testLine;

            return;
        }

        if (currentLine) {
            lines.push(currentLine);
        }

        currentLine = word;
    });

    if (currentLine) {
        lines.push(currentLine);
    }

    const visibleLines = lines.slice(0, maxLines);

    if (lines.length > maxLines && visibleLines.length > 0) {
        const lastIndex = visibleLines.length - 1;
        let lastLine = visibleLines[lastIndex];

        while (context.measureText(`${lastLine}...`).width > maxWidth && lastLine.length > 0) {
            lastLine = lastLine.slice(0, -1).trim();
        }

        visibleLines[lastIndex] = `${lastLine}...`;
    }

    visibleLines.forEach((line, index) => {
        context.fillText(line, x, y + (index * lineHeight));
    });

    return y + (visibleLines.length * lineHeight);
}

function drawJakkaLogo(context, x, y, size) {
    context.save();
    context.font = `900 ${size}px Inter, Arial, sans-serif`;
    context.fillStyle = '#ffffff';
    context.fillText('JAKKA', x, y);

    const jakkaWidth = context.measureText('JAKKA').width + 12;
    const letters = [
        ['S', '#40E0D0'],
        ['P', '#FF0000'],
        ['A', '#FF69B4'],
        ['C', '#00FF00'],
        ['E', '#8A2BE2'],
    ];
    let cursor = x + jakkaWidth;

    letters.forEach(([letter, color]) => {
        context.fillStyle = color;
        context.fillText(letter, cursor, y);
        cursor += context.measureText(letter).width;
    });

    context.restore();
}

function drawJakkaInlineLogo(context, x, y, size) {
    context.save();
    context.font = `400 ${size}px "Peace Sans", Inter, Arial, sans-serif`;
    context.textBaseline = 'alphabetic';
    context.fillStyle = '#ffffff';
    context.fillText('JAKKA', x, y);

    const jakkaWidth = context.measureText('JAKKA').width + 10;
    const letters = [
        ['S', '#40E0D0'],
        ['P', '#FF0000'],
        ['A', '#FF69B4'],
        ['C', '#00FF00'],
        ['E', '#8A2BE2'],
    ];
    let cursor = x + jakkaWidth;

    letters.forEach(([letter, color]) => {
        context.fillStyle = color;
        context.fillText(letter, cursor, y);
        cursor += context.measureText(letter).width;
    });

    context.restore();
}

function drawStoryPill(context, width) {
    const logoSize = 58;
    context.save();
    context.font = `400 ${logoSize}px "Peace Sans", Inter, Arial, sans-serif`;

    const letters = [
        ['S', '#40E0D0'],
        ['P', '#FF0000'],
        ['A', '#FF69B4'],
        ['C', '#00FF00'],
        ['E', '#8A2BE2'],
    ];
    const jakkaWidth = context.measureText('JAKKA').width + 16;
    const spaceWidth = letters.reduce((total, [letter]) => total + context.measureText(letter).width, 0);
    const logoWidth = jakkaWidth + spaceWidth;
    const pillWidth = logoWidth + 112;
    const pillHeight = 112;
    const x = (width - pillWidth) / 2;
    const y = 142;

    context.shadowColor = 'rgba(0, 0, 0, 0.38)';
    context.shadowBlur = 30;
    context.shadowOffsetY = 16;
    roundedPath(context, x, y, pillWidth, pillHeight, 18);
    context.fillStyle = 'rgba(8, 10, 12, 0.94)';
    context.fill();

    context.shadowColor = 'transparent';
    context.strokeStyle = 'rgba(255,255,255,0.9)';
    context.lineWidth = 10;
    context.beginPath();
    context.moveTo(x + 36, y + 24);
    context.lineTo(x + pillWidth - 36, y + 24);
    context.moveTo(x + 36, y + pillHeight - 25);
    context.lineTo(x + pillWidth - 36, y + pillHeight - 25);
    context.stroke();

    context.textBaseline = 'alphabetic';
    context.fillStyle = 'rgba(255,255,255,0.96)';
    let cursor = x + ((pillWidth - logoWidth) / 2);
    const baseline = y + 75;

    context.fillText('JAKKA', cursor, baseline);
    cursor += jakkaWidth;

    letters.forEach(([letter, color]) => {
        context.fillStyle = color;
        context.fillText(letter, cursor, baseline);
        cursor += context.measureText(letter).width;
    });

    context.restore();
}

function shortStoryGenres(genres) {
    return String(genres || '')
        .split(',')
        .map((genre) => genre.trim())
        .filter(Boolean)
        .slice(0, 2)
        .join(' / ');
}

function drawCenteredWrappedText(context, text, centerX, y, maxWidth, lineHeight, maxLines) {
    const words = String(text || '').split(/\s+/).filter(Boolean);
    const lines = [];
    let currentLine = '';

    words.forEach((word) => {
        const testLine = currentLine ? `${currentLine} ${word}` : word;

        if (context.measureText(testLine).width <= maxWidth) {
            currentLine = testLine;

            return;
        }

        if (currentLine) {
            lines.push(currentLine);
        }

        currentLine = word;
    });

    if (currentLine) {
        lines.push(currentLine);
    }

    const visibleLines = lines.slice(0, maxLines);

    if (lines.length > maxLines && visibleLines.length > 0) {
        const lastIndex = visibleLines.length - 1;
        let lastLine = visibleLines[lastIndex];

        while (context.measureText(`${lastLine}...`).width > maxWidth && lastLine.length > 0) {
            lastLine = lastLine.slice(0, -1).trim();
        }

        visibleLines[lastIndex] = `${lastLine}...`;
    }

    visibleLines.forEach((line, index) => {
        context.fillText(line, centerX, y + (index * lineHeight));
    });

    return y + (visibleLines.length * lineHeight);
}

async function drawStoryTemplate() {
    if (! storyCanvas || ! storyButton) {
        return;
    }

    const context = storyCanvas.getContext('2d');
    const width = storyCanvas.width;
    const height = storyCanvas.height;
    const data = storyButton.dataset;

    setStoryStatus('Membuat template...');

    if (document.fonts?.ready) {
        await document.fonts.ready.catch(() => {});
    }

    const [backdrop, poster] = await Promise.all([
        loadStoryImage(data.storyBackdrop || data.storyPoster),
        loadStoryImage(data.storyPoster),
    ]);

    context.clearRect(0, 0, width, height);

    const baseGradient = context.createLinearGradient(0, 0, width, height);
    baseGradient.addColorStop(0, '#26333a');
    baseGradient.addColorStop(0.48, '#101417');
    baseGradient.addColorStop(1, '#020303');
    context.fillStyle = baseGradient;
    context.fillRect(0, 0, width, height);

    if (backdrop) {
        context.save();
        context.globalAlpha = 0.34;
        context.filter = 'blur(30px) saturate(0.82)';
        drawCoverImage(context, backdrop, -48, -48, width + 96, height + 96);
        context.restore();
    }

    const overlay = context.createLinearGradient(0, 0, 0, height);
    overlay.addColorStop(0, 'rgba(21,29,34,0.28)');
    overlay.addColorStop(0.46, 'rgba(4,6,8,0.58)');
    overlay.addColorStop(0.78, 'rgba(0,0,0,0.94)');
    overlay.addColorStop(1, 'rgba(0,0,0,1)');
    context.fillStyle = overlay;
    context.fillRect(0, 0, width, height);

    drawStoryPill(context, width);

    const posterWidth = 700;
    const posterHeight = 1050;
    const posterX = (width - posterWidth) / 2;
    const posterY = 340;

    context.save();
    context.shadowColor = 'rgba(0, 0, 0, 0.68)';
    context.shadowBlur = 50;
    context.shadowOffsetY = 34;
    roundedPath(context, posterX, posterY, posterWidth, posterHeight, 24);
    context.fillStyle = 'rgba(255,255,255,0.08)';
    context.fill();
    context.restore();

    if (poster) {
        drawRoundedImage(context, poster, posterX, posterY, posterWidth, posterHeight, 24);
    } else {
        roundedPath(context, posterX, posterY, posterWidth, posterHeight, 24);
        context.fillStyle = '#151515';
        context.fill();
        context.fillStyle = 'rgba(255,255,255,0.48)';
        context.font = '400 48px "Bebas Neue", Arial, sans-serif';
        context.textAlign = 'center';
        context.fillText('NO POSTER', width / 2, posterY + (posterHeight / 2));
        context.textAlign = 'left';
    }

    context.textAlign = 'center';
    context.fillStyle = '#ffffff';
    context.font = '900 54px Inter, Arial, sans-serif';
    let currentY = drawCenteredWrappedText(
        context,
        data.storyTitle || 'Tanpa Judul',
        width / 2,
        1478,
        width - 150,
        60,
        2,
    );

    const meta = [data.storyYear, shortStoryGenres(data.storyGenres)]
        .filter(Boolean)
        .join(' / ');

    context.fillStyle = 'rgba(255,255,255,0.78)';
    context.font = '900 34px Inter, Arial, sans-serif';
    context.fillText(`Rating ${data.storyRating || '0.0'} / 10`, width / 2, currentY + 42);

    context.fillStyle = 'rgba(255,255,255,0.56)';
    context.font = '700 28px Inter, Arial, sans-serif';
    context.fillText(meta || 'Film pilihan komunitas', width / 2, currentY + 94);

    if (data.storyDirector) {
        context.fillStyle = 'rgba(255,255,255,0.56)';
        context.font = '500 27px Inter, Arial, sans-serif';
        drawCenteredWrappedText(
            context,
            `Sutradara ${data.storyDirector}`,
            width / 2,
            currentY + 146,
            width - 180,
            38,
            2,
        );
    }

    context.fillStyle = 'rgba(255,255,255,0.48)';
    context.font = '700 22px Inter, Arial, sans-serif';
    context.fillText('FILM DIARY BY JAKKA SPACE', width / 2, 1844);
    context.textAlign = 'left';

    setStoryStatus('Template siap.');
}

function getStoryBlob() {
    return new Promise((resolve, reject) => {
        if (! storyCanvas) {
            reject(new Error('Canvas tidak tersedia.'));

            return;
        }

        storyCanvas.toBlob((blob) => {
            if (! blob) {
                reject(new Error('Template belum bisa dibuat.'));

                return;
            }

            resolve(blob);
        }, 'image/png', 0.95);
    });
}

async function downloadStoryTemplate() {
    if (! storyButton) {
        return;
    }

    setStoryBusy(true);

    try {
        await drawStoryTemplate();
        const blob = await getStoryBlob();
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');

        anchor.href = url;
        anchor.download = `${storySlug(storyButton.dataset.storyTitle)}-story.png`;
        anchor.click();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
        setStoryStatus('Template diunduh.');
    } catch {
        setStoryStatus('Template belum bisa diunduh.');
    } finally {
        setStoryBusy(false);
    }
}

async function shareStoryTemplate() {
    if (! storyButton) {
        return;
    }

    setStoryBusy(true);

    try {
        await drawStoryTemplate();
        const blob = await getStoryBlob();
        const file = new File([blob], `${storySlug(storyButton.dataset.storyTitle)}-story.png`, {
            type: 'image/png',
        });

        if (navigator.canShare?.({ files: [file] })) {
            await navigator.share({
                files: [file],
                title: storyButton.dataset.storyTitle || 'Jakka Space',
            });
            setStoryStatus('Template dibagikan.');
        } else {
            await downloadStoryTemplate();
            setStoryStatus('Share belum didukung, template diunduh.');
        }
    } catch {
        setStoryStatus('Template belum bisa dibagikan.');
    } finally {
        setStoryBusy(false);
    }
}

function openStoryModal() {
    if (! storyModal) {
        return;
    }

    storyModal.hidden = false;
    document.body.classList.add('story-modal-open');
    drawStoryTemplate().catch(() => setStoryStatus('Template belum bisa dibuat.'));
}

function closeStoryModal() {
    if (! storyModal) {
        return;
    }

    storyModal.hidden = true;
    document.body.classList.remove('story-modal-open');
}

storyButton?.addEventListener('click', openStoryModal);
storyDownloadButton?.addEventListener('click', downloadStoryTemplate);
storyNativeButton?.addEventListener('click', shareStoryTemplate);
storyCloseButtons.forEach((button) => button.addEventListener('click', closeStoryModal));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && storyModal && ! storyModal.hidden) {
        closeStoryModal();
    }
});
