// Add active class to current navigation item
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'home';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(`page=${currentPage}`)) {
            link.classList.add('active');
        }
    });
});

// Show loading spinner for form submissions
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.classList.contains('needs-loading')) {
        const spinner = document.querySelector('.spinner-overlay');
        if (spinner) {
            spinner.style.display = 'flex';
        }
    }
});

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Initialize popovers
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
});

// Confirm delete actions
function confirmDelete(event, message) {
    if (!confirm(message || 'Are you sure you want to delete this item?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Handle responsive tables
function makeTableResponsive() {
    const tables = document.querySelectorAll('.table-responsive-custom');
    tables.forEach(table => {
        const headerCells = table.querySelectorAll('thead th');
        const dataCells = table.querySelectorAll('tbody td');
        
        headerCells.forEach((header, index) => {
            const headerText = header.textContent;
            dataCells.forEach((cell, cellIndex) => {
                if (cellIndex % headerCells.length === index) {
                    cell.setAttribute('data-label', headerText);
                }
            });
        });
    });
}

// Initialize responsive tables
document.addEventListener('DOMContentLoaded', makeTableResponsive); 