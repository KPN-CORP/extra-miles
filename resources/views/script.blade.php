
{{-- <script src="{{ asset('js/report.js') }}"></script> --}}
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

        const editButtonsSocial = document.querySelectorAll('.edit-social-btn');

        editButtonsSocial.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const category = this.getAttribute('data-category');
                const businessUnit = this.getAttribute('data-businessUnit');
                const link = this.getAttribute('data-link');

                // Isi form
                document.getElementById('edit-category').value = category;
                document.getElementById('edit-businessunit').value = businessUnit;
                document.getElementById('edit-link').value = link;

                // Ubah action form sesuai ID
                const form = document.getElementById('editSocialForm');
                form.action = `/admin/social/${id}`;
            });
        });

        document.querySelectorAll('.archive-btn').forEach(button => {
            button.addEventListener('click', function () {
                const quoteId = this.dataset.id;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This data will be archived.",
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
                    'link',
                    '|',
                    'undo', 'redo'
                ],
                removePlugins: ['Image', 'ImageToolbar', 'EasyImage', 'ImageUpload', 'MediaEmbed', 'CKFinder']
            })
            .catch(error => {
                console.error(error);
            });
    });

    document.addEventListener('DOMContentLoaded', function () {
        ClassicEditor
            .create(document.querySelector('#newsHeadline'), {
                toolbar: [
                    'bold', 'italic', 'underline', 'strikethrough',
                    '|',
                    'undo', 'redo'
                ],
                removePlugins: ['Image', 'ImageToolbar', 'EasyImage', 'ImageUpload', 'MediaEmbed', 'CKFinder']
            })
            .catch(error => {
                console.error(error);
            });
    });

    function previewImage(event) {
        const file = event.target.files[0];

        if (file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Check file type
            if (!allowedTypes.includes(file.type)) {
                alert("Please select a valid image file (JPG, PNG, GIF, or WEBP).");
                event.target.value = ''; // Clear file input
                return;
            }

            // Check file size
            if (file.size > maxSize) {
                alert("File size must be less than or equal to 2MB.");
                event.target.value = ''; // Clear file input
                return;
            }

            // Show image preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewContainer = document.getElementById('image-preview-container');
                const previewImage = document.getElementById('image-preview');
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
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

    //view Form Schema di tabel
    $(document).on('click', '.view-schema', function () {
        const title = $(this).data('title');
        
        const schema = $(this).data('schema');
        console.log($(this).data('schema'));
        $('#schemaModalLabel').text(title);
        let content = '';
    
        if (schema.fields && schema.fields.length > 0) {
            schema.fields.forEach((field, index) => {
                content += `
                    <div class="mb-3">
                        <strong>${index + 1}. ${field.label}</strong><br>
                        <em>Type:</em> ${field.type} |
                        <em>Required:</em> ${field.required ? 'Yes' : 'No'} |
                        <em>Validation:</em> ${field.validation || '-'}
                    </div>
                `;
            });
        } else {
            content = '<p class="text-muted">No fields available.</p>';
        }
    
        $('#schemaFields').html(content);
        $('#viewSchemaModal').modal('show');
    });

    $(document).ready(function () {
        $('#add-row').click(function () {
            let rowCount = $('.form-row-item').length;
            let newRow = $('.form-row-item').first().clone();

            newRow.find('input, select').val('');
            newRow.find('input[name^="required"]').attr('name', `required[${rowCount}]`);
            newRow.find('input[type="checkbox"]').prop('checked', false).val('1');
            newRow.find('.options-wrapper').addClass('d-none').find('input').val('');
            newRow.find('.options-confirmation').addClass('d-none');
            newRow.find('.checkbox-confirmation').addClass('d-none');
            newRow.find('.remove-row').show();
            $('#form-builder-wrapper').append(newRow);
        });

        $(document).ready(function () {
            $(document).on('change', 'select[name="type[]"]', function () {
                let selectedType = $(this).val();
                let wrapper = $(this).closest('.form-row-item');
                let optionsDiv = wrapper.find('.options-wrapper');
                let optionsConf = wrapper.find('.options-confirmation');
                let confirmationSection = wrapper.find('.checkbox-confirmation');

                if (selectedType === 'checkbox') {
                    optionsDiv.removeClass('d-none');
                    optionsConf.addClass('d-none');
                    confirmationSection.addClass('d-none');
                    confirmationSection.find('[name="type_confirmation[]"], [name="label_confirmation[]"]').prop('required', false);
                } else if (selectedType === 'radio') {
                    optionsDiv.removeClass('d-none');
                    optionsConf.removeClass('d-none');
                } else {
                    optionsDiv.addClass('d-none');
                    optionsConf.addClass('d-none');
                    confirmationSection.addClass('d-none');
                    optionsDiv.find('input').val('');
                    confirmationSection.find('[name="type_confirmation[]"], [name="label_confirmation[]"]').prop('required', false);
                }
            });

            $(document).on('change', '.options-confirmation', function () {
                const container = $(this).closest('.form-row-item');
                const show = $(this).is(':checked');
                const input = container.find('input.text-confirmation');
                const confirmationSection = container.find('.checkbox-confirmation');

                confirmationSection.toggleClass('d-none', !show);
                confirmationSection.find('[name="type_confirmation[]"], [name="label_confirmation[]"]').prop('required', show);
                input.val('text');
            });
        });

        // Event delegasi untuk tombol remove
        $(document).on('click', '.remove-row', function () {
            if ($('.form-row-item').length > 1) {
                $(this).closest('.form-row-item').remove();
            }
        });
    });

    $(document).ready(function () {
        $('#add-row-edit').click(function () {
            let rowCount = $('.form-row-item').length;
            let newRow = $('.form-row-item').first().clone();

            newRow.find('input, select').each(function () {
                if ($(this).is(':checkbox')) {
                    $(this).prop('checked', false);
                } else {
                    $(this).val('');
                }
            });

            newRow.find('.options-wrapper').addClass('d-none');
            newRow.find('.options-confirmation').addClass('d-none');
            newRow.find('.checkbox-confirmation').addClass('d-none');
            newRow.find('.remove-row').show();

            $('#form-builder-wrapper').append(newRow);

            updateInputNames();
        });

        $(document).on('change', 'select[name^="type"]', function () {
            let selectedType = $(this).val();
            let wrapper = $(this).closest('.form-row-item');
            let optionsDiv = wrapper.find('.options-wrapper');
            let optionsConf = wrapper.find('.options-confirmation');
            let confirmationSection = wrapper.find('.checkbox-confirmation');

            if (selectedType === 'checkbox') {
                optionsDiv.removeClass('d-none');
                optionsConf.addClass('d-none');
                confirmationSection.addClass('d-none');
                confirmationSection.find('[name^="type_confirmation"], [name^="label_confirmation"]').prop('required', false);
            } else if (selectedType === 'radio') {
                optionsDiv.removeClass('d-none');
                optionsConf.removeClass('d-none');
            } else {
                optionsDiv.addClass('d-none');
                optionsConf.addClass('d-none');
                confirmationSection.addClass('d-none');
                optionsDiv.find('input').val('');
                confirmationSection.find('[name^="type_confirmation"], [name^="label_confirmation"]').prop('required', false);
            }
        });

        $(document).on('change', '.options-confirmation', function () {
            const container = $(this).closest('.form-row-item');
            const show = $(this).is(':checked');
            const input = container.find('input.text-confirmation');
            const input_label = container.find('input.label-confirm');
            const confirmationSection = container.find('.checkbox-confirmation');

            confirmationSection.toggleClass('d-none', !show);
            confirmationSection.find('[name^="type_confirmation"], [name^="label_confirmation"]').prop('required', show);
            input.val('text');
            input_label.val('');
        });

        function updateInputNames() {
            $('#form-builder-wrapper .form-row-item').each(function(index) {
                $(this).find('select[name^="type"]').attr('name', `type[${index}]`);
                $(this).find('input[name^="edit_label"]').attr('name', `edit_label[${index}]`);
                $(this).find('input[name^="options"]').attr('name', `options[${index}]`);
                $(this).find('input[name^="validation"]').attr('name', `validation[${index}]`);
                $(this).find('input[name^="required"]').attr('name', `required[${index}]`);
                $(this).find('input[name^="confirmation"]').attr('name', `confirmation[${index}]`);
                $(this).find('input[name^="label_confirmation"]').attr('name', `label_confirmation[${index}]`);                
            });
        }

        $(document).on('click', '.remove-row', function () {
            if ($('.form-row-item').length > 1) {
                $(this).closest('.form-row-item').remove();
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const checkbox = document.getElementById('custom_form');
        const formSelectWrapper = document.getElementById('form-select-wrapper');
        const formPreviewWrapper = document.getElementById('form-preview-wrapper');
        const formSelect = document.getElementById('form_id');
        const formPreview = document.getElementById('form-preview');
    
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                formSelectWrapper.classList.remove('d-none');
                formPreviewWrapper.classList.remove('d-none');
            } else {
                formSelectWrapper.classList.add('d-none');
                formPreviewWrapper.classList.add('d-none');
                formPreview.innerHTML = '';
                formSelect.value = '';
            }
        });
    
        formSelect.addEventListener('change', function () {
            const formId = this.value;
            if (!formId) return;
    
            fetch(`/admin/forms/${formId}/schema`)
                .then(response => {
                    console.log('Raw response:', response);
                    if (!response.ok) throw new Error("Fetch error: " + response.status);
                    return response.json();
                })
                .then(data => {
                    const previewDiv = document.getElementById('form-preview');
                    previewDiv.innerHTML = '';
    
                    data.fields.forEach(field => {
                        const fieldWrapper = document.createElement('div');
                        fieldWrapper.className = 'mb-3';
    
                        const label = document.createElement('label');
                        label.className = 'form-label';
                        label.textContent = field.label;
                        fieldWrapper.appendChild(label);
    
                        let input;
                        if (field.type === 'textarea') {
                            input = document.createElement('textarea');
                            input.className = 'form-control';
                        } else {
                            input = document.createElement('input');
                            input.type = field.type;
                            input.className = 'form-control';
                        }
    
                        input.name = field.name;
                        // Abaikan required agar bisa submit tanpa isian
                        // input.required = field.required || false;
    
                        fieldWrapper.appendChild(input);
                        previewDiv.appendChild(fieldWrapper);
                    });
                })
                .catch(error => {
                    console.error('Gagal load schema:', error);
                });
        });
    
        // Trigger otomatis jika ada form_id saat halaman edit
        if (formSelect && formSelect.value) {
            // checkbox.checked = true;
            // formSelectWrapper.classList.remove('d-none');
            // formPreviewWrapper.classList.remove('d-none');
            formSelect.dispatchEvent(new Event('change'));
        }
    });

    const eventSelectAllCheckbox = document.getElementById('selectAll');
    const eventRowCheckboxes = document.querySelectorAll('.row-checkbox');
    const eventApproveSelectedBtn = document.getElementById('approveSelectedBtn');
    const eventQuotaMax = {{ isset($event) ? $event->quota : 0 }};
    const eventApprovedCount = {{ isset($countApproved) ? $countApproved : 0 }};
    const eventRemainingQuota = eventQuotaMax - eventApprovedCount;

    function updateEventApproveButton() {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        eventApproveSelectedBtn.textContent = `Approve Selected (${selectedCount})`;
        eventApproveSelectedBtn.disabled = selectedCount === 0;
    }

    if (eventSelectAllCheckbox) {
        eventSelectAllCheckbox.addEventListener('change', function () {
            eventRowCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateEventApproveButton();
        });
    }

    eventRowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (!this.checked) {
                eventSelectAllCheckbox.checked = false;
            }
            updateEventApproveButton();
        });
    });

    // Tombol Approve Selected
    eventApproveSelectedBtn.addEventListener('click', function (e) {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;

        if (selectedCount > eventRemainingQuota) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Quota Exceeded',
                html: `Kuota tersisa hanya <b>${eventRemainingQuota}</b> peserta.<br>
                       Kamu memilih <b>${selectedCount}</b> peserta.`,
                confirmButtonText: 'OK'
            });
            return false;
        }

        // Jika valid: lanjutkan
        // Contoh (ganti sesuai kebutuhan):
        // Swal.fire({
        //     icon: 'success',
        //     title: 'Valid!',
        //     text: 'Peserta dapat diproses.',
        //     showConfirmButton: false,
        //     timer: 1500
        // });

        // TODO: submit form atau trigger aksi lainnya
    });

    function submitApproveParticipant(actionUrl) {
        Swal.fire({
            title: 'Approve This Participant?',
            text: "The participant will be moved to the 'Confirmation' status.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = actionUrl;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';

                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>