// Mouse move effect for cards with 3D rotation
document.addEventListener('mousemove', e => {
    document.querySelectorAll('.stat-card, .chart-card').forEach(card => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        // Calculate rotation angles based on mouse position
        const rotateX = ((y - rect.height / 2) / rect.height) * 10;
        const rotateY = ((x - rect.width / 2) / rect.width) * 10;
        
        // Set CSS variables for the hover effect
        card.style.setProperty('--rotate-x', -rotateX);
        card.style.setProperty('--rotate-y', rotateY);
        card.style.setProperty('--mouse-x', `${x}px`);
        card.style.setProperty('--mouse-y', `${y}px`);
    });
});

// Immediate animation on page load
document.addEventListener('DOMContentLoaded', () => {
    // Add animation classes to cards
    const cards = document.querySelectorAll('.stat-card, .chart-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(50px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Add shine effect to cards
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
            
            // Add dynamic shadow based on mouse position
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const shadowX = (x - centerX) / 10;
            const shadowY = (y - centerY) / 10;
            
            card.style.boxShadow = `
                ${shadowX}px ${shadowY}px 30px rgba(220, 53, 69, 0.3),
                0 10px 20px rgba(0, 0, 0, 0.1)
            `;
        });

        card.addEventListener('mouseleave', () => {
            card.style.boxShadow = '';
        });
    });

    // Enhanced icon animations
    const icons = document.querySelectorAll('.stat-card-icon');
    icons.forEach(icon => {
        icon.addEventListener('mouseover', () => {
            icon.style.transform = 'scale(1.2) rotate(15deg)';
            icon.style.boxShadow = '0 0 20px rgba(220, 53, 69, 0.4)';
        });
        
        icon.addEventListener('mouseout', () => {
            icon.style.transform = '';
            icon.style.boxShadow = '';
        });
    });
});

// Enhanced background animation
let mouseX = 0;
let mouseY = 0;
let rafId = null;

document.addEventListener('mousemove', (e) => {
    mouseX = e.clientX / window.innerWidth;
    mouseY = e.clientY / window.innerHeight;
    
    if (!rafId) {
        rafId = requestAnimationFrame(updateBackground);
    }
});

function updateBackground() {
    const moveX = mouseX * 40;
    const moveY = mouseY * 40;
    
    document.body.style.backgroundPosition = `${moveX}% ${moveY}%`;
    document.body.style.setProperty('--mouse-x', `${mouseX * 100}%`);
    document.body.style.setProperty('--mouse-y', `${mouseY * 100}%`);
    
    rafId = null;
}

// Add ripple effect to buttons
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function(e) {
        const rect = button.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const ripple = document.createElement('span');
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        ripple.className = 'ripple';
        
        button.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 1000);
    });
});

// Smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
}); 