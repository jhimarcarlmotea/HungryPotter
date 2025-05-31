<?php
// update_address.php - Separate file for handling address updates
require 'db.php';
session_start();

// Set JSON content type
header('Content-Type: application/json');

// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if this is an address update request
if (!isset($_POST['action']) || $_POST['action'] !== 'update_address') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Get and validate the new address
$new_address = isset($_POST['address']) ? trim($_POST['address']) : '';

if (empty($new_address)) {
    echo json_encode(['success' => false, 'message' => 'Address cannot be empty']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Check if database connection exists
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }
    
    // Prepare and execute the update query
    $update_address_query = "UPDATE sign_up SET address = ? WHERE userId = ?";
    $stmt = $conn->prepare($update_address_query);
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param("si", $new_address, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Address updated successfully',
                'new_address' => $new_address
            ]);
        } else {
            // Check if user exists
            $check_user = "SELECT userId FROM sign_up WHERE userId = ?";
            $check_stmt = $conn->prepare($check_user);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Address was not changed (same value)']);
            }
            $check_stmt->close();
        }
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Address update error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}

// Close database connection if needed
if (isset($conn)) {
    $conn->close();
}
?>
<script>
    // Updated Edit address functionality - now points to separate PHP file
const editAddressBtn = document.getElementById('edit-address-btn');
const addressField = document.getElementById('address');
let isEditing = false;
let originalAddress = addressField.value;

editAddressBtn.addEventListener('click', function() {
    isEditing = !isEditing;
    
    if (isEditing) {
        addressField.readOnly = false;
        addressField.style.backgroundColor = '#fff';
        addressField.style.border = '1px solid var(--primary-red)';
        editAddressBtn.textContent = 'Save Address';
        editAddressBtn.classList.remove('btn-outline');
        editAddressBtn.classList.add('btn');
        addressField.focus();
    } else {
        // Save the address
        const newAddress = addressField.value.trim();
        
        if (newAddress && newAddress !== originalAddress) {
            // Show loading state
            editAddressBtn.textContent = 'Saving...';
            editAddressBtn.disabled = true;
            
            // Create FormData object
            const formData = new FormData();
            formData.append('action', 'update_address');
            formData.append('address', newAddress);
            
            // Send AJAX request to separate handler file
            fetch('update_address.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Log the actual response for debugging
                    return response.text().then(text => {
                        console.log('Non-JSON response:', text);
                        throw new Error('Server returned HTML instead of JSON. Check server logs.');
                    });
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                
                if (data.success) {
                    originalAddress = newAddress;
                    // Show success feedback
                    editAddressBtn.textContent = 'Address Saved!';
                    editAddressBtn.style.backgroundColor = 'var(--green)';
                    
                    setTimeout(() => {
                        editAddressBtn.textContent = 'Edit Address';
                        editAddressBtn.style.backgroundColor = '';
                        editAddressBtn.classList.add('btn-outline');
                        editAddressBtn.classList.remove('btn');
                    }, 2000);
                } else {
                    console.error('Server error:', data);
                    alert('Failed to save address: ' + (data.message || 'Unknown error'));
                    addressField.value = originalAddress;
                    resetEditButton();
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while saving the address: ' + error.message);
                addressField.value = originalAddress;
                resetEditButton();
            })
            .finally(() => {
                editAddressBtn.disabled = false;
                addressField.readOnly = true;
                addressField.style.backgroundColor = '';
                addressField.style.border = '1px solid #ddd';
                isEditing = false;
            });
        } else {
            // No changes made, just toggle back
            resetEditButton();
        }
    }
});

// Helper function to reset the edit button
function resetEditButton() {
    addressField.readOnly = true;
    addressField.style.backgroundColor = '';
    addressField.style.border = '1px solid #ddd';
    editAddressBtn.textContent = 'Edit Address';
    editAddressBtn.classList.add('btn-outline');
    editAddressBtn.classList.remove('btn');
    isEditing = false;
}
</script>