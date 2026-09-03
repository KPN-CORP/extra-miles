{{-- Generic confirm-then-submit for the wellness admin pages.
     Mark a button with class="js-confirm" (or js-archive), data-form="<form id>"
     and an optional data-text. Swal is exposed globally by resources/js/app.js. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-confirm, .js-archive').forEach(function (button) {
            button.addEventListener('click', function () {
                var form = document.getElementById(this.dataset.form);

                if (!form) {
                    return;
                }

                if (typeof window.Swal === 'undefined') {
                    if (window.confirm(this.dataset.text || 'Are you sure?')) {
                        form.submit();
                    }
                    return;
                }

                window.Swal.fire({
                    title: this.dataset.title || 'Are you sure?',
                    text: this.dataset.text || 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ab2f2b',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: this.dataset.confirm || 'Yes, continue'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
