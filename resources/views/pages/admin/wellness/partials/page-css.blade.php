{{--
    Shared styling for the wellness admin pages.

    The theme's page header (layouts_/shared/topbar -> .page-title-box) puts the
    breadcrumb in a right-floated .page-title-right and never clears it. Bootstrap's
    .row is display:flex, so it establishes a block formatting context -- and a BFC
    box that starts beside a float is shortened by that float's width. Any page whose
    content begins high enough to sit next to the breadcrumb therefore loses ~180px
    off its right edge. Clearing the page container fixes it for good, whatever the
    page starts with.

    Done here rather than in scss/custom/components/_page-title.scss because the SCSS
    needs `yarn build` to take effect, and assets are pre-built for deployment.
--}}
<style>
    #content > .container-fluid {
        clear: both;
    }

    /* CKEditor sizes itself to its content; give it a comfortable writing area. */
    .ck-editor__editable_inline {
        min-height: 220px;
    }

    /* Selected visibility card in the activity form. */
    .form-check.border-primary {
        box-shadow: 0 0 0 1px var(--ct-primary);
    }

    /* --- Activity create form ------------------------------------------- */

    /* Keep Save reachable however long the sessions list grows. */
    .wa-actions {
        position: sticky;
        bottom: 0;
        z-index: 5;
        padding: .75rem 1rem;
        border: 1px solid var(--ct-border-color);
        border-radius: var(--ct-border-radius);
        background-color: var(--ct-secondary-bg);
        box-shadow: 0 -2px 8px rgb(0 0 0 / 6%);
    }

    /* Dashed outline reads as a drop target / placeholder rather than a real card. */
    .border-dashed {
        border-style: dashed !important;
    }

    /* Lets the summary line ellipsize instead of pushing the row buttons off. */
    .min-w-0 {
        min-width: 0;
    }

    /* The whole session header is the collapse affordance, not just the chevron. */
    .js-schedule-row > .card-header {
        cursor: default;
    }

    .js-schedule-toggle {
        text-decoration: none;
        line-height: 1;
    }

    .js-schedule-row.border-danger {
        box-shadow: 0 0 0 1px var(--ct-danger);
    }

    /* Tabs carry a small warning glyph when their pane holds an error. */
    #wa_tabs .nav-link .js-tab-error {
        font-size: .85em;
        vertical-align: baseline;
    }

    /* --- Participant feedback tab --------------------------------------- */

    /* Feedback is prose, so it has to wrap -- the other wellness tables are
       nowrap and would stretch the page to the width of the longest message. */
    .wf-message {
        white-space: normal;
        min-width: 20rem;
        max-width: 40rem;
    }
</style>
