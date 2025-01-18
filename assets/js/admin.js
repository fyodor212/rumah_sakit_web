// Function untuk menampilkan detail booking dalam modal
function showBookingDetails(bookingId) {
    // Tampilkan loading spinner
    const modalContent = document.querySelector('#bookingDetailModal .modal-content');
    modalContent.innerHTML = `
        <div class="d-flex justify-content-center align-items-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById('bookingDetailModal'));
    modal.show();
    
    // Ambil detail booking menggunakan Fetch API
    fetch(`index.php?page=admin/get_booking_details&id=${bookingId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', text);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            if (data.status === 'success') {
                modalContent.innerHTML = data.html;
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="modal-header">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        ${error.message || 'Terjadi kesalahan saat memuat data. Silakan coba lagi.'}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            `;
        });
}

// Function untuk menghapus dokter
function deleteDoctor(doctorId, doctorName) {
    Swal.fire({
        title: 'Hapus Dokter',
        html: `Yakin ingin menghapus dokter <strong>${doctorName}</strong>?<br><small class="text-muted">Semua data terkait dokter ini juga akan dihapus</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            try {
                // Siapkan form data
                const formData = new URLSearchParams();
                formData.append('doctor_id', doctorId);

                // Kirim request
                const response = await fetch('index.php?page=admin/handle_delete_doctor', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Ambil response text
                const text = await response.text();
                
                // Coba parse sebagai JSON
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    if (text.includes('login.php') || text.includes('<!DOCTYPE html>')) {
                        window.location.href = 'index.php?page=auth/login';
                        return;
                    }
                    throw new Error('Terjadi kesalahan pada server');
                }

                // Cek status response
                if (!response.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan saat menghapus data');
                }

                // Cek status operasi
                if (data.status !== 'success') {
                    throw new Error(data.message || 'Gagal menghapus data dokter');
                }

                return data;
            } catch (error) {
                Swal.showValidationMessage(error.message);
                throw error;
            }
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: result.value.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.reload();
            });
        }
    }).catch(error => {
        if (error && error.message) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: error.message
            });
        }
    });
}

// Initialize DataTables dengan bahasa Indonesia
$(document).ready(function() {
    if ($.fn.DataTable) {
        // Inisialisasi DataTables dengan konfigurasi default
        $.extend(true, $.fn.DataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
            },
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]]
        });
        
        // Inisialisasi semua tabel dengan class datatable
        $('.datatable').DataTable();
    }
    
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Handle klik tombol edit dokter
    $('.edit-doctor').click(function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const spesialisasi = $(this).data('spesialisasi');
        const hari = $(this).data('hari') ? $(this).data('hari').split(', ') : [];
        const jadwal = $(this).data('jadwal') ? $(this).data('jadwal').split(' - ') : ['', ''];
        const status = $(this).data('status');

        // Reset form
        $('#editDoctorForm')[0].reset();
        
        // Set nilai ke form
        $('#editDoctorId').val(id);
        $('#editNama').val(nama);
        $('#editSpesialisasi').val(spesialisasi);
        
        // Check hari praktik
        $('.hari-praktik').prop('checked', false);
        hari.forEach(h => {
            $(`input[value="${h}"]`).prop('checked', true);
        });
        
        // Set jam praktik
        $('#editJamMulai').val(jadwal[0]);
        $('#editJamSelesai').val(jadwal[1]);
        $('#editStatus').val(status);
        
        // Tampilkan modal
        $('#editDoctorModal').modal('show');
    });

    // Handle submit form edit
    $('#saveEdit').click(function() {
        // Validasi form
        const form = $('#editDoctorForm');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        // Ambil data form
        const formData = new FormData(form[0]);
        const selectedHari = [];
        $('.hari-praktik:checked').each(function() {
            selectedHari.push($(this).val());
        });
        formData.set('hari', selectedHari);

        // Debug log
        console.log('Form data:', Object.fromEntries(formData));

        // Tampilkan loading
        const saveBtn = $(this);
        const originalText = saveBtn.html();
        saveBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...').prop('disabled', true);

        // Kirim data ke server
        fetch('index.php?page=admin/handle_edit_doctor', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Response text:', text);
                        throw new Error('Terjadi kesalahan saat menyimpan data');
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: error.message || 'Terjadi kesalahan saat menyimpan data'
            });
        })
        .finally(() => {
            saveBtn.html(originalText).prop('disabled', false);
        });
    });
}); 