// DOM Ready Function
document.addEventListener('DOMContentLoaded', function() {
    // Your JavaScript code goes here

    // Example: Toggle password visibility
    const passwordField = document.getElementById('password');
    const togglePassword = document.getElementById('toggle-password');

    if (passwordField && togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Show Password' : 'Hide Password';
        });
    }

    // Example: Validate email format
    const emailField = document.getElementById('email');
    const emailError = document.getElementById('email-error');

    if (emailField) {
        emailField.addEventListener('blur', function() {
            const email = this.value.trim();
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regex.test(email)) {
                emailError.textContent = 'Invalid email format';
            } else {
                emailError.textContent = '';
            }
        });
    }
});

// Other Functions
function showAlert(message, type) {
    // Function to display alerts (e.g., success, error)
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);

    // Remove alert after 3 seconds
    setTimeout(function() {
        alertDiv.remove();
    }, 3000);
}
// Example JavaScript for QR code scanning and attendance marking
function handleQRScan(qrContent) {
    // Decode QR content and extract event ID or details
    var eventData = JSON.parse(qrContent); // Assuming qrContent is JSON

    // Display event details on UI (eventData should contain event details)

    // Mark attendance via AJAX or form submission
    var eventID = eventData.event_id; // Extract event ID from QR content
    var attendeeID = getUserId(); // Replace with actual attendee ID

    // Send attendance marking request to server
    $.ajax({
        type: "POST",
        url: "mark_attendance.php", // Replace with your server-side script
        data: { event_id: eventID, attendee_id: attendeeID },
        success: function(response) {
            alert("Attendance marked successfully!");
            // Optionally, handle UI updates or feedback
        },
        error: function(xhr, status, error) {
            alert("Failed to mark attendance: " + error);
        }
    });
}

// Replace with actual QR code scanning library integration
function scanQRCode() {
    // Example placeholder function for QR code scanning
    // Assumes qrScanner is your QR code scanning library
    qrScanner.scan(function(result) {
        handleQRScan(result); // Pass scanned QR content to handle function
    });
}
// Wait for the DOM to be fully loaded
$(document).ready(function() {
    // Attach a click event handler to the generate button
    $('#generateButton').on('click', function(e) {
        // Prevent the form from being submitted
        e.preventDefault();

        // Get the src attribute of the QR code image
        var qrCodeSrc = $('.card-body img').attr('src');

        // Check if the QR code exists
        if (qrCodeSrc) {
            // Create a SweetAlert2 pop-up with the QR code
            Swal.fire({
                title: 'Your QR Code',
                html: '<img src="' + qrCodeSrc + '" alt="QR Code">',
                confirmButtonText: 'Close'
            });
        } else {
            // If no QR code exists, show an error message
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'No QR code has been generated yet!'
            });
        }
    });
});
document.addEventListener('DOMContentLoaded', (event) => {
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal 
    document.querySelectorAll('.open-modal').forEach(button => {
        button.onclick = function() {
            var eventData = JSON.parse(this.getAttribute('data-event'));
            document.getElementById('modal-event-name').textContent = 'Event Name: ' + eventData.event_name;
            document.getElementById('modal-event-date').textContent = 'Event Date: ' + eventData.event_date;
            document.getElementById('modal-event-location').textContent = 'Event Location: ' + eventData.event_location;
            modal.style.display = "block";
        }
    });

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});
