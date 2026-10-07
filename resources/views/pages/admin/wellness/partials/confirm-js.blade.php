{{-- Generic confirm-then-submit for the wellness admin pages.
     Mark a button with class="js-confirm" (or js-archive), data-form="<form id>"
     and an optional data-text. Swal is exposed globally by resources/js/app.js. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Delegated, not bound per button: DataTables keeps rows of other
        // pages (and re-sorted or collapsed ones) outside the document when
        // this runs, so a direct listener would miss them.
        document.addEventListener('click', function (event) {
            var button = event.target.closest('.js-confirm, .js-archive');
            if (!button) {
                return;
            }

            var form = document.getElementById(button.dataset.form);

            if (!form) {
                return;
            }

            if (typeof window.Swal === 'undefined') {
                if (window.confirm(button.dataset.text || @json(__('Are you sure?')))) {
                    form.submit();
                }
                return;
            }

            window.Swal.fire({
                title: button.dataset.title || @json(__('Are you sure?')),
                text: button.dataset.text || @json(__('This action cannot be undone.')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ab2f2b',
                cancelButtonColor: '#aaa',
                confirmButtonText: button.dataset.confirm || @json(__('Yes, continue'))
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
