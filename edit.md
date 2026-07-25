//buat view lampiran
ini file view/permohonan_invoice.php
            $.ajax({
                url: '../models/permohonan/save_permohonan.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(res) {
                    if(res.status === 'success') {
                       
                        // LANGKAH 2: Upload file ke server IT (IP 234)
                        var fileInput = $('#lampiran')[0];
                        var fileDokumen = (fileInput && fileInput.files) ? fileInput.files[0] : null;
                       
                        if (fileDokumen) {
                            btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Mengirim File ke Pusat...');
                           
                            var formPusat = new FormData();
                            /**
                             * MENYESUAIKAN FILE save_upload.php IT:
                             * - Variabel file harus 'lampiran[]' (karena IT pakai count array)
                             * - Variabel ID harus 'id_input'
                             */
                            formPusat.append('lampiran[]', fileDokumen);
                            formPusat.append('id_input', res.id_permohonan);

                            $.ajax({
                                url: 'http://192.168.2.234/upload/siak/save_upload.php',
                                type: 'POST',
                                data: formPusat,
                                processData: false,
                                contentType: false,
                                // Menggunakan 'complete' karena skrip IT melakukan redirect window.location
                                complete: function() {
                                    Swal.fire('Berhasil', res.message, 'success').then(function() {
                                        loadContent('monitoring_permohonan.php');
                                    });
                                }
                            });
                        } else {
                            // Berhasil tanpa file
                            Swal.fire('Berhasil', res.message, 'success').then(function() {
                                loadContent('monitoring_permohonan.php');
                            });
                        }
                    } else {
                        btn.prop('disabled', false).html(originalHtml);
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalHtml);
                    console.log("Raw Response: " + xhr.responseText);
                    Swal.fire({
                        title: 'Eror Sistem',
                        text: 'Cek Console F12 untuk detail.',
                        icon: 'error'
                    });
                }
            });
