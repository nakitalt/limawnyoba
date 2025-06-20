// script.js - File JavaScript eksternal
document.addEventListener('DOMContentLoaded', function() {
    // Validasi form tambah/edit
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validasi nama
            const nama = form.querySelector('#nama');
            if (nama && nama.value.trim().length < 3) {
                isValid = false;
                showError(nama, 'Nama minimal 3 karakter');
            }
            
            // Validasi email
            const email = form.querySelector('#email');
            if (email && !validateEmail(email.value)) {
                isValid = false;
                showError(email, 'Format email tidak valid');
            }
            
            // Validasi telepon
            const telepon = form.querySelector('#telepon');
            if (telepon && !validatePhone(telepon.value)) {
                isValid = false;
                showError(telepon, 'Hanya angka yang diperbolehkan');
            }
            
            if (!isValid) {
                e.preventDefault();
                showToast('Harap perbaiki kesalahan pada form', 'error');
            }
        });
    });

    // Fungsi validasi email
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Fungsi validasi telepon
    function validatePhone(phone) {
        const re = /^[0-9]{10,15}$/;
        return re.test(phone);
    }

    // Tampilkan error pada input
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;
        
        let errorElement = formGroup.querySelector('.error-message');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            errorElement.style.color = '#dc3545';
            errorElement.style.fontSize = '0.8em';
            errorElement.style.marginTop = '5px';
            formGroup.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        input.style.borderColor = '#dc3545';
        
        input.addEventListener('input', function() {
            errorElement.textContent = '';
            input.style.borderColor = '#e9ecef';
        }, { once: true });
    }

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '1';
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }, 3000);
        }, 100);
    }
});