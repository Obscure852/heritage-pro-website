// Example: How to access school info API from JavaScript
async function getSchoolInfo() {
    try {
        const response = await fetch('/api/school/info', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + getAuthToken(), // Get token from storage/cookies
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            console.log('School Info:', data.data);
            return data.data;
        } else {
            throw new Error(data.message || 'Failed to fetch school info');
        }
    } catch (error) {
        console.error('Error fetching school info:', error);
        throw error;
    }
}

// Helper function to get auth token (implement based on your auth system)
function getAuthToken() {
    // Example: Get from localStorage, sessionStorage, or cookies
    return localStorage.getItem('auth_token') ||
           sessionStorage.getItem('auth_token') ||
           getCookie('auth_token');
}

// Helper function to get cookie value
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

// Usage example
document.addEventListener('DOMContentLoaded', function() {
    // Get school info when page loads
    getSchoolInfo()
        .then(schoolData => {
            // Update UI with school data
            updateSchoolInfoUI(schoolData);
        })
        .catch(error => {
            console.error('Failed to load school info:', error);
        });
});

function updateSchoolInfoUI(data) {
    // Example: Update UI elements
    if (document.getElementById('school-name')) {
        document.getElementById('school-name').textContent = data.school_name || '';
    }

    if (document.getElementById('school-slogan')) {
        document.getElementById('school-slogan').textContent = data.slogan || '';
    }

    // Add more UI updates as needed
}
