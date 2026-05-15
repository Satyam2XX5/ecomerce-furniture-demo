</div> <!-- End admin-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
<script>
// Mobile sidebar
const toggle   = document.getElementById('sidebar-toggle');
const sidebar  = document.getElementById('admin-sidebar');
const overlay  = document.getElementById('sidebar-overlay');

function openSidebar() {
    sidebar.classList.add('open');
    overlay.style.display = 'block';
}
function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.style.display = 'none';
}

if (toggle) toggle.addEventListener('click', openSidebar);

// Show toggle on mobile
function checkMobile() {
    if (window.innerWidth <= 768) {
        toggle.style.display = 'block';
    } else {
        toggle.style.display = 'none';
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
    }
}
window.addEventListener('resize', checkMobile);
checkMobile();
</script>
</body>
</html>