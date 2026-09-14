export function buildQrCheckUrl(token) {
    return `/reception/check/${encodeURIComponent(token)}`;
}
