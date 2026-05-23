  </div><!-- /.admin-content -->
</div><!-- /.admin-main -->

<!-- Sidebar overlay for mobile -->
<div id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999" onclick="document.querySelector('.admin-sidebar').classList.remove('open');this.style.display='none'"></div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
<script>
window.APP_URL    = '<?= APP_URL ?>';
window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
// Enhanced sidebar toggle for admin
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  document.getElementById('adminSidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').style.display =
    document.getElementById('adminSidebar').classList.contains('open') ? 'block' : 'none';
});
</script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body></html>
