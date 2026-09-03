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
</style>
