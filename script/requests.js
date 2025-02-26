// requests.js
document.getElementById('startOrder').addEventListener('click', function() {
    alert('Order process started!');
});

// Example of making an AJAX request
function submitFeedback() {
    const formData = new FormData(document.getElementById('feedbackForm'));
    fetch('submit_feedback.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.text())
    .then(data => {
        alert('Feedback submitted successfully');
        window.location.reload();
    });
}

// Add similar listener for feedback submission
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitFeedback();
});