export function extractReservationLookup(rawValue) {
    const raw = String(rawValue || '').trim();

    if (!raw) {
        return '';
    }

    const receptionMatch = raw.match(/\/reception\/check\/([^/?#]+)/i);

    if (receptionMatch) {
        try {
            return decodeURIComponent(receptionMatch[1]);
        } catch {
            return receptionMatch[1];
        }
    }

    try {
        const parsed = new URL(raw);
        const segments = parsed.pathname.split('/').filter(Boolean);
        const last = segments.pop() || '';

        return last ? decodeURIComponent(last) : raw;
    } catch {
        return raw;
    }
}

export function buildQrCheckUrl(token) {
    return `/reception/check/${encodeURIComponent(token)}`;
}
