function togglePassword(inputId, el) {
        const input = document.getElementById(inputId);
        const icon = el.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('ti-eye', 'ti-lock');
        } else {
            input.type = 'password';
            icon.classList.replace('ti-lock', 'ti-eye');
        }
    }