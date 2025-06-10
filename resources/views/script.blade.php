
{{-- <script src="{{ asset('js/report.js') }}"></script> --}}
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

@if(Session::has('toast'))
<script>
    const toastData = {!! json_encode(Session::get('toast')) !!};

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
    });

    Toast.fire({
        icon: toastData.type,
        title: toastData.message
    });
</script>
@endif

<script>
    // script QR Code
    function showQRModal(token) {
        document.getElementById("qrcode").innerHTML = "";

        const dummyURL = `https://example.com/ticket/${token}`;
        
        new QRCode(document.getElementById("qrcode"), {
            text: dummyURL,
            width: 300,
            height: 300,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        const linkEl = document.getElementById("dummyLink");
        linkEl.href = dummyURL;
        linkEl.textContent = dummyURL;

        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    }

    // script Archive Event
    document.addEventListener("DOMContentLoaded", function () {
        const editButtons = document.querySelectorAll('.edit-quote-btn');
        const form = document.getElementById('editQuoteForm');
        const authorInput = document.getElementById('edit-author');
        const quotesInput = document.getElementById('edit-quotes');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const author = this.dataset.author;
                const quotes = this.dataset.quotes;

                // Isi data ke form
                authorInput.value = author;
                quotesInput.value = quotes;

                // Update form action
                form.action = `/admin/quotes/${id}`;
            });
        });

        document.querySelectorAll('.archive-btn').forEach(button => {
            button.addEventListener('click', function () {
                const quoteId = this.dataset.id;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This quote will be archived.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ab2f2b',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('archive-form-' + quoteId).submit();
                    }
                });
            });
        });

        document.querySelectorAll(".btn-archive").forEach(function (button) {
            button.addEventListener("click", function () {
                const eventId = this.getAttribute("data-id");
    
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Event will be archived.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ab2f2b',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-form-${eventId}`).submit();
                    }
                });
            });
        });

        document.querySelectorAll(".btn-close-reg").forEach(function (btn) {
            btn.addEventListener("click", function () {
                const id = this.dataset.id;
                const action = this.dataset.action;
                const message = action === 'close' 
                    ? 'Close registration for this event?' 
                    : 'Reopen registration for this event?';

                Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ab2f2b',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Yes, continue'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`close-form-${id}`).submit();
                    }
                });
            });
        });

        function updateDateTime() {
            const now = new Date();

            // Format time
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            // Update DOM elements
            const currentTimeEl = document.getElementById('currentTime');
            if (currentTimeEl) {
                currentTimeEl.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }

        // Update immediately and then every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
    });

</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Set tab kembali ke active setelah reload
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            const triggerEl = document.querySelector(`[data-bs-target="${activeTab}"]`);
            const tabPane = document.querySelector(`${activeTab}`);

            if (triggerEl && tabPane) {
                document.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active', 'show'));

                triggerEl.classList.add('active');
                tabPane.classList.add('active', 'show');
            }

            localStorage.removeItem('activeTab');
        }

        // Saat tombol canceled diklik
        document.querySelectorAll('.canceled-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                localStorage.setItem('activeTab', '#approved');
            });
        });
    });
</script>

@if (session('success'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                confirmButtonColor: '#ab2f2b',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
                confirmButtonColor: '#ab2f2b',
                confirmButtonText: 'OK'
            });
        });
    </script>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function () {
      const startInput = document.getElementById('start_date');
      const endInput = document.getElementById('end_date');
  
      startInput.addEventListener('change', function () {
        endInput.min = this.value;
      });
  
      endInput.addEventListener('change', function () {
        if (this.value < startInput.value) {
          alert("End Date tidak boleh kurang dari Start Date!");
          this.value = '';
        }
      });
    });

    document.addEventListener('DOMContentLoaded', function () {
        ClassicEditor
            .create(document.querySelector('#description'), {
                toolbar: [
                    'heading',
                    '|',
                    'bold', 'italic', 'underline', 'strikethrough',
                    '|',
                    'bulletedList', 'numberedList',
                    '|',
                    'undo', 'redo'
                ],
                removePlugins: ['Image', 'ImageToolbar', 'EasyImage', 'ImageUpload', 'MediaEmbed', 'CKFinder']
            })
            .catch(error => {
                console.error(error);
            });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');
        const participantButtons = document.querySelectorAll('#v-tabs button[data-status]');

        function filterParticipants() {
            const selectedStatus = statusFilter.value;
            const searchQuery = searchInput.value.toLowerCase();

            participantButtons.forEach(button => {
                const status = button.getAttribute('data-status');
                const name = button.getAttribute('data-name');

                const matchesStatus = selectedStatus === 'All' || status === selectedStatus;
                const matchesSearch = name.includes(searchQuery);

                if (matchesStatus && matchesSearch) {
                    button.style.display = 'block';
                } else {
                    button.style.display = 'none';
                }
            });
        }

        statusFilter.addEventListener('change', filterParticipants);
        searchInput.addEventListener('input', filterParticipants);
    });

    document.querySelectorAll('.archive-live-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This will archive the live content.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ab2f2b',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, archive it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('archive-live-form-' + id).submit();
                }
            });
        });
    });
</script>