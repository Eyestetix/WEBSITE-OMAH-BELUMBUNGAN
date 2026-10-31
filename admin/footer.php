  </main>
</div>

<?php 
// Include shared media picker modal for all admin pages
require_once __DIR__ . '/media_picker_modal.php'; 
?>

<script>
  // Mobile Navigation Drawer Controller
  (function() {
    const toggleBtn = document.getElementById('admMobileToggle');
    const sidebar = document.getElementById('admSidebar');
    const backdrop = document.getElementById('admDrawerBackdrop');

    if (toggleBtn && sidebar && backdrop) {
      function openDrawer() {
        sidebar.classList.add('open');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
      function closeDrawer() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
      }

      toggleBtn.addEventListener('click', openDrawer);
      backdrop.addEventListener('click', closeDrawer);

      window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
          closeDrawer();
        }
      });
    }
  })();
</script>
</body>
</html>
