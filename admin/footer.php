            </div><!-- /.admin-page-content -->
        </div><!-- /.admin-content -->
    </div><!-- /.admin-wrapper -->

    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
    // Admin sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        if (toggle && sidebar) {
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }
        // Auto-dismiss flash messages
        const flash = document.getElementById('flashMessage');
        if (flash) {
            setTimeout(function() { flash.style.display = 'none'; }, 5000);
        }
    });
    </script>
</body>
</html>
