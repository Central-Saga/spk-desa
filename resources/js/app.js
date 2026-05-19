import 'bootstrap';

// Sidebar toggle untuk mobile
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.querySelector('[data-spk-sidebar-toggle]');
    const sidebar = document.querySelector('.spk-sidebar');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }

    // Auto-dismiss alerts setelah 5 detik
    document.querySelectorAll('.alert.auto-dismiss').forEach((el) => {
        setTimeout(() => {
            const alert = bootstrap.Alert.getOrCreateInstance(el);
            alert.close();
        }, 5000);
    });
});
