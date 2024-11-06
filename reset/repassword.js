
document.addEventListener('DOMContentLoaded', function() {
    var eyeIcons = document.querySelectorAll('.fas.fa-eye-slash');
    
    eyeIcons.forEach(function(eyeIcon) {
        eyeIcon.addEventListener('click', function() {
            var passwordInput = this.previousElementSibling;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            }
        });
    });
});
