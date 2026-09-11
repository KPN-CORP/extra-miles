{{--
    Shared DataTables initializer for admin list pages.
    Pushed via @push('scripts') so it runs after jQuery (loaded in script.blade.php).
    Any <table class="js-datatable"> on the page gets consistent
    search / sort / pagination. Mark <th class="no-sort"> to disable sorting on a column.
--}}
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
    (function () {
        function initAdminDataTables() {
            if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.DataTable) {
                return;
            }
            var $ = window.jQuery;

            $('table.js-datatable').each(function () {
                if ($.fn.DataTable.isDataTable(this)) {
                    return;
                }
                $(this).DataTable({
                    responsive: true,
                    autoWidth: false,
                    order: [],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, @json(__('All'))]],
                    columnDefs: [{ orderable: false, targets: 'no-sort' }],
                    language: {
                        search: '',
                        searchPlaceholder: @json(__('Search...')),
                        lengthMenu: @json(__('_MENU_ entries')),
                        info: @json(__('Showing _START_ to _END_ of _TOTAL_ entries')),
                        infoEmpty: @json(__('Showing 0 entries')),
                        infoFiltered: @json(__('(filtered from _MAX_ total)')),
                        zeroRecords: @json(__('No matching records found')),
                        emptyTable: @json(__('No data available')),
                        paginate: {
                            previous: "<i class='ri-arrow-left-s-line'></i>",
                            next: "<i class='ri-arrow-right-s-line'></i>"
                        }
                    },
                    drawCallback: function () {
                        $(this).closest('.dataTables_wrapper')
                            .find('.dataTables_paginate').addClass('pagination-rounded');
                    }
                });
            });

            // Recalculate column widths when a Bootstrap tab becomes visible
            // (tables rendered inside hidden tabs otherwise mis-size their columns).
            $('button[data-bs-toggle="tab"]').off('shown.bs.tab.admindt')
                .on('shown.bs.tab.admindt', function () {
                    $.fn.dataTable.tables({ visible: true, api: true })
                        .columns.adjust().responsive.recalc();
                });
        }

        if (document.readyState !== 'loading') {
            initAdminDataTables();
        } else {
            document.addEventListener('DOMContentLoaded', initAdminDataTables);
        }
    })();
</script>
