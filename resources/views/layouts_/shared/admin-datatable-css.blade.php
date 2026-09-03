{{-- Shared styling for admin list pages (tabs + DataTables). Included via @section('css'). --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<style>
    /* Brand-consistent tabs */
    .nav-tabs .nav-link.active {
        background-color: #ab2f2b !important;
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.375rem;
    }
    .nav-tabs .nav-link {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Consistent admin tables */
    table.dataTable thead th {
        white-space: nowrap;
        vertical-align: middle;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        min-width: 220px;
    }
    div.dataTables_wrapper div.dataTables_length select {
        min-width: 4.5rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button .page-link {
        border-radius: 0.375rem;
    }
    /* Keep header/body borders tidy inside cards */
    table.dataTable > thead > tr > th {
        border-bottom-width: 1px;
    }
</style>
