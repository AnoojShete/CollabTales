// Dark mode toggle
$(document).ready(function() {
    $('#darkModeToggle').click(function() {
        $('body').toggleClass('dark-mode');
        const isDarkMode = $('body').hasClass('dark-mode');
        document.cookie = `darkMode=${isDarkMode ? 'enabled' : 'disabled'}; path=/; max-age=${60*60*24*365}`;
        $(this).html(isDarkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>');
    });
    
    // Logout functionality
    $('#logout').click(function(e) {
        e.preventDefault();
        $.post('/api/logout.php', function() {
            window.location.href = '/views/login.php';
        });
    });
    
    // Initialize dark mode from cookie
    const darkModeCookie = document.cookie.split('; ').find(row => row.startsWith('darkMode='));
    if (darkModeCookie && darkModeCookie.split('=')[1] === 'enabled') {
        $('body').addClass('dark-mode');
        $('#darkModeToggle').html('<i class="fas fa-sun"></i>');
    }
});

// AJAX error handling
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    if (jqxhr.status === 401) {
        alert('Your session has expired. Please log in again.');
        window.location.href = '/views/login.php';
    } else if (jqxhr.responseJSON && jqxhr.responseJSON.error) {
        alert('Error: ' + jqxhr.responseJSON.error);
    } else {
        alert('An error occurred. Please try again.');
    }
});