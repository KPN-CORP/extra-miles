<div id="preloader" class="bg-light bg-opacity-75">
    <div id="status">
        <div class="bouncing-loader">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div>

<script>
    // Owns the admin preloader end to end. Two problems it solves:
    //
    //  1. It used to be dismissed on `window.onload`, which waits for every
    //     subresource -- the jQuery / DataTables CDN scripts, the Vite bundle,
    //     the icon webfonts. Admin tables are rendered server-side in Blade, so
    //     the rows paint long before that, and because the overlay is
    //     translucent (.bg-opacity-75) you end up watching the spinner sit on
    //     top of data you can already read. DOMContentLoaded is the real signal.
    //
    //  2. The overlay ships inside the *next* page's HTML, so on its own it can
    //     only appear once the server has already responded -- the click-to-
    //     first-byte gap, which is the slow part, showed nothing at all. So the
    //     outgoing page raises it the moment a navigation starts.
    //
    // Lives here rather than in resources/js/script.js because Blade views ship
    // straight to the server while that bundle needs `yarn build` first.
    (function () {
        // A navigation that never happens (cancelled, or a link that turns out
        // to serve a file) must not leave the overlay up for good.
        var SAFETY_MS = 20000;

        var navigating = false;
        var safety = null;

        function show() {
            var preloader = document.getElementById('preloader');
            var status = document.getElementById('status');

            if (preloader) {
                preloader.style.display = '';
            }

            if (status) {
                status.style.display = '';
            }
        }

        function hide() {
            // While we are on our way out, "this page is ready" is stale news.
            if (navigating) {
                return;
            }

            var preloader = document.getElementById('preloader');

            if (preloader) {
                preloader.style.display = 'none';
            }
        }

        function release() {
            navigating = false;

            if (safety) {
                clearTimeout(safety);
                safety = null;
            }
        }

        function showForNavigation() {
            navigating = true;
            show();

            if (safety) {
                clearTimeout(safety);
            }

            safety = setTimeout(function () {
                release();
                hide();
            }, SAFETY_MS);
        }

        // script.js re-exports these; keeping the names means the topbar's
        // onclick="showLoader()" and the ajax callers in report.js / layer.js /
        // goal-approval.js keep working. They get the plain show, not the
        // navigation one, so their matching hideLoader() still lands.
        window.adminPreloader = { show: show, hide: hide };
        window.showLoader = show;
        window.hideLoader = hide;

        // --- Dismiss once this page is usable -------------------------------

        function hideAfterPaint() {
            // Lets the synchronous DOMContentLoaded handlers finish first --
            // notably the shared DataTables initializer -- so a table is never
            // revealed in its raw, un-paginated state.
            if (typeof window.requestAnimationFrame === 'function') {
                window.requestAnimationFrame(hide);
            } else {
                hide();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hideAfterPaint);
        } else {
            hideAfterPaint();
        }

        window.addEventListener('load', hide);

        window.addEventListener('pageshow', function (event) {
            // Back/forward out of the bfcache restores the page with the overlay
            // we raised on the way out, and DOMContentLoaded does not fire again.
            if (event.persisted) {
                release();
                hide();
            }
        });

        // --- Raise it the moment a navigation starts ------------------------

        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0) {
                return;
            }

            // Modified clicks open a tab or save the target; this page stays put.
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            var link = event.target.closest ? event.target.closest('a[href]') : null;

            if (!link) {
                return;
            }

            // Collapse/dropdown/modal triggers, downloads, new tabs, and links
            // that opt out (file exports, which leave the page where it is).
            if (link.hasAttribute('download') ||
                link.hasAttribute('data-no-loader') ||
                link.hasAttribute('data-bs-toggle')) {
                return;
            }

            if (link.target && link.target !== '_self') {
                return;
            }

            var href = link.getAttribute('href');

            if (!href || href.charAt(0) === '#') {
                return;
            }

            var url;

            try {
                url = new URL(link.href, window.location.href);
            } catch (error) {
                return;
            }

            // mailto:, tel:, javascript: and anything off-origin never replace
            // this document.
            if (url.protocol !== 'http:' && url.protocol !== 'https:') {
                return;
            }

            if (url.origin !== window.location.origin) {
                return;
            }

            // Same document, different fragment: a scroll, not a load.
            if (url.hash && url.href.split('#')[0] === window.location.href.split('#')[0]) {
                return;
            }

            showForNavigation();
        });

        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (event.defaultPrevented || !form || form.hasAttribute('data-no-loader')) {
                return;
            }

            if (form.target && form.target !== '_self') {
                return;
            }

            showForNavigation();
        });
    })();
</script>
