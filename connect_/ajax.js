if (files.length > 0) {
    var formData = new FormData();
    formData.append('id_agenda', id_header);
    formData.append('usercreated', sessionNIK);

    files.forEach(function(file) {
        formData.append('files[]', file);
    });

    // Upload file ke server file
    $.ajax({
        url: "http://192.168.2.234/upload/agenda_marketing/upload.php",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(uploadResponse) {
            var pesan = uploadResponse.map(f => f.file + ": " + f.status).join("\n");
            alert("Jadwal tersimpan.\nStatus upload file:\n" + pesan);
            tampilAwal();
            $(".modal_tambah_jadwal").modal("hide");
        },
        error: function(xhr, status, error) {
            console.error("XHR:", xhr.responseText);
            console.error("Status:", status);
            console.error("Error:", error);
            alert("Jadwal berhasil disimpan, tapi gagal upload file ke server file.");
        }
    });
} else {
    alert("Jadwal berhasil disimpan.");
    tampilAwal();
    $(".modal_tambah_jadwal").modal("hide");
}
