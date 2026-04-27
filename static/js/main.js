document.addEventListener('DOMContentLoaded', function() {
    const closeButtons = document.querySelectorAll('.close-alert');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.remove();
        });
    });

    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);

    const mobileMenu = document.querySelector('.mobile-menu');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenu) {
        mobileMenu.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }

    const ratingInputs = document.querySelectorAll('.rating input');
    const ratingStars = document.querySelectorAll('.rating-stars .star');
    
    if (ratingInputs.length > 0) {
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const stars = this.closest('.rating-stars').querySelectorAll('.star');
                stars.forEach((star, index) => {
                    if (index < this.value) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            });
        });
    }

    if (ratingStars.length > 0) {
        ratingStars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                const form = this.closest('form');
                const input = form.querySelector('input[name="rating"]');
                if (input) {
                    input.value = rating;
                }
                ratingStars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }

    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
});

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.toggle('hidden');
    }
}