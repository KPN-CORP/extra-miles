import axios from 'axios';

// In-memory cache for the wellness endpoints. It exists for two reasons:
//
//  1. AnimatePresence runs with mode="wait", so a route only mounts *after* the
//     previous page has finished its exit animation. Without a prefetch the
//     request does not even leave the browser until that animation is over, and
//     the skeleton then has to sit through the whole round trip on top of it.
//     Calling prefetch* on tap starts the request while the animation plays.
//
//  2. Coming back to a page we have already loaded should not show a skeleton
//     again. Pages seed their state from the cache and revalidate in the
//     background, so the data on screen stays fresh without a loading flash.
//
// The cache lives for the session only (a page reload clears it), which matches
// how the SPA holds its token.

const cache = new Map();
const inflight = new Map();

export const ACTIVITIES_KEY = 'activities';
export const MY_REGISTRATIONS_KEY = 'my-registrations';
export const activityKey = (id) => `activity:${id}`;

const paths = {
    [ACTIVITIES_KEY]: '/api/wellness/activities',
    [MY_REGISTRATIONS_KEY]: '/api/wellness/my-registrations',
};

const pathFor = (key) =>
    paths[key] ?? `/api/wellness/activities/${encodeURIComponent(key.slice('activity:'.length))}`;

export function getCached(key) {
    return cache.get(key);
}

/**
 * Resolves with the endpoint payload, sharing a single request when the same
 * key is asked for twice before the first one lands (tap-then-mount does this).
 */
export function loadWellness(key, apiUrl, token, { force = false } = {}) {
    if (!force && inflight.has(key)) {
        return inflight.get(key);
    }

    const request = axios
        .get(`${apiUrl}${pathFor(key)}`, { headers: { Authorization: `Bearer ${token}` } })
        .then((res) => {
            cache.set(key, res.data);
            return res.data;
        })
        .finally(() => {
            if (inflight.get(key) === request) {
                inflight.delete(key);
            }
        });

    inflight.set(key, request);

    return request;
}

/** Fire-and-forget warm-up. Failures are ignored; the page will retry and report. */
export function prefetchWellness(key, apiUrl, token) {
    if (!apiUrl || !token || cache.has(key) || inflight.has(key)) return;

    loadWellness(key, apiUrl, token).catch(() => {});
}

/**
 * Drop cached payloads after a write. Seat counts and statuses live on both the
 * list and the detail response, so a registration invalidates more than one key.
 */
export function invalidateWellness(keys) {
    (Array.isArray(keys) ? keys : [keys]).forEach((key) => {
        cache.delete(key);
        inflight.delete(key);
    });
}
