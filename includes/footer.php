            </div>
            <!-- /Main Content Area -->

        </div>
        <!-- /#content-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- JS Scripts (Bootstrap 4 relies on jQuery + Popper.js) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Application JS -->
    <script src="/sp_umum/assets/js/app.js?v=3"></script>

    <?php if (!empty($switch_roles_json)): ?>
    <script>
    (function() {
        var spRoles = <?php echo json_encode($switch_roles_json); ?>;
        var btnSwitch = document.getElementById('btnSwitchRole');
        if (!btnSwitch || !spRoles.length) return;

        btnSwitch.addEventListener('click', function(e) {
            e.preventDefault();

            var iconMap = { 1: 'fa-shield-alt', 2: 'fa-briefcase', 3: 'fa-shopping-cart' };
            var colorMap = { 1: '#2563eb', 2: '#4f46e5', 3: '#16a34a' };

            var html = '<div style="display:flex; flex-direction:column; gap:10px; padding:6px 0;">';
            spRoles.forEach(function(r) {
                var icon = iconMap[r.id] || 'fa-user';
                var color = colorMap[r.id] || '#64748b';
                var isActive = r.aktif;
                var border = isActive ? '2px solid ' + color : '2px solid #e2e8f0';
                var bg = isActive ? '#f0f9ff' : '#fff';
                var badge = isActive ? '<span style="font-size:0.65rem; background:' + color + '; color:#fff; padding:2px 8px; border-radius:20px; margin-left:auto; font-weight:600;">AKTIF</span>' : '<i class="fas fa-chevron-right" style="color:#cbd5e1; margin-left:auto;"></i>';

                html += '<button type="button" class="btn-switch-role" data-id="' + r.id + '" ' +
                    (isActive ? 'disabled ' : '') +
                    'style="display:flex; align-items:center; gap:14px; width:100%; padding:14px 16px; border:' + border + '; ' +
                    'border-radius:12px; background:' + bg + '; text-align:left; cursor:' + (isActive ? 'default' : 'pointer') + '; transition:all 0.2s ease; outline:none; opacity:' + (isActive ? '0.7' : '1') + ';">' +
                    '<div style="width:38px; height:38px; border-radius:10px; background:' + (isActive ? color : '#f1f5f9') + '; display:flex; align-items:center; justify-content:center; color:' + (isActive ? '#fff' : color) + '; font-size:1rem; flex-shrink:0;">' +
                    '<i class="fas ' + icon + '"></i></div>' +
                    '<div style="flex:1;"><div style="font-weight:700; color:#1e293b; font-size:0.9rem;">' + r.nama + '</div>' +
                    '<div style="font-size:0.72rem; color:#94a3b8; font-weight:500;">' + (isActive ? 'Sedang aktif' : 'Beralih ke ' + r.nama) + '</div></div>' +
                    badge + '</button>';
            });
            html += '</div>';

            Swal.fire({
                title: '<i class="fas fa-exchange-alt" style="color:#818cf8; margin-right:6px;"></i> Ganti Role',
                html: html,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Batal',
                cancelButtonColor: '#94a3b8',
                customClass: { popup: 'swal-switch-role' },
                didOpen: function() {
                    var btns = document.querySelectorAll('.btn-switch-role:not([disabled])');
                    btns.forEach(function(btn) {
                        btn.addEventListener('mouseenter', function() {
                            this.style.borderColor = '#bfdbfe';
                            this.style.background = '#f0f9ff';
                            this.style.transform = 'translateY(-1px)';
                            this.style.boxShadow = '0 2px 8px rgba(37,99,235,0.08)';
                        });
                        btn.addEventListener('mouseleave', function() {
                            this.style.borderColor = '#e2e8f0';
                            this.style.background = '#fff';
                            this.style.transform = 'translateY(0)';
                            this.style.boxShadow = 'none';
                        });
                        btn.addEventListener('click', function() {
                            var roleId = parseInt(this.getAttribute('data-id'));
                            doSwitchRole(roleId);
                        });
                    });
                }
            });
        });

        function doSwitchRole(roleId) {
            Swal.fire({
                title: 'Mengganti role...',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/sp_umum/admin/aksi_switch_role.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res[0] === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Role Diganti!',
                                text: res[1],
                                timer: 1000,
                                showConfirmButton: false
                            }).then(function() {
                                window.location.href = '/sp_umum/home.php?page=dashboard';
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res[1], confirmButtonColor: '#2563eb' });
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan server.', confirmButtonColor: '#2563eb' });
                    }
                }
            };
            xhr.send('role_id=' + roleId);
        }
    })();
    </script>
    <?php endif; ?>
</body>
</html>
