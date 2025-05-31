<?php
session_start();
require 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: Login.php");
    exit;
}

// Handle delete action
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sign_up WHERE userId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    header("Location: Manageusers.php?msg=User deleted successfully");
    exit;
}

// Handle update action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $userId = $_POST['userId'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("UPDATE sign_up SET firstName = ?, lastName = ?, address = ?, phoneNumber = ?, email = ?, role = ? WHERE userId = ?");
    $stmt->bind_param("ssssssi", $firstName, $lastName, $address, $phoneNumber, $email, $role, $userId);
    $stmt->execute();
    header("Location: Manageusers.php?msg=User updated successfully");
    exit;
}

// Fetch all users
$query = "SELECT userId, firstName, lastName, address, phoneNumber, email, role FROM sign_up";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Users</title>
    <script src="script.js?v=<?php echo filemtime('script.js'); ?>" defer></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="globals.css" />

    <style>        
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: Arial, sans-serif;
        background-color: #ffffff;
    }
      .back-to-home {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #e74c3c;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: background-color 0.3s;
  }
        
  .back-to-home:hover {
    background-color: #c0392b;
  }
           .admin-panel {
            background-color: #ffffff;
            padding: 20px 0;
            border-bottom: 3px solid #8B0000;
            margin-bottom: 20px;
                font-family: "Lato", Helvetica;
                align-items:center;
        }
        
        .category-tabs {
            max-width: 1200px;
            margin: 0 auto;
            margin-right:200px
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
                    font-family: "Lato", Helvetica;
                    margin-left:200px;
        }
        
        .category-tab {
            padding: 12px 24px;
            border: 2px solid #8B0000;
            border-radius: 30px;
            background-color: #fff;
            color: #8B0000;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            
        }
        
        .category-tab.active,
        .category-tab:hover {
            background-color: #8B0000;
            color: #fff;
        }
        
        .category-tab i {
            font-size: 16px;
        }
        
        /* Admin Panel Title */
        .admin-title {
            padding: 12px 24px;
            border: 2px solid #8B0000;
            border-radius: 30px;
            background-color: #8B0000;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: default;
        font-family: "Lato", Helvetica;
        }
        
        /* Menu Section */
        .menu-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-top: <?php echo $isAdmin ? '60px' : '0'; ?>;
        }
        
        .category-title {
            text-align: center;
            margin: 40px 0 30px;
        }
        
        .category-title h2 {
            font-size: 36px;
            color: #8B0000;
            text-transform: uppercase;
        }
        
        .category-title p {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }
        
        .logout-btn, .login-btn {
            padding: 8px 16px;
            background-color: #8B0000;
            color: #fff;
            border: none;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .logout-btn:hover, .login-btn:hover {
            background-color: #660000;
            transform: translateY(-2px);
        }
        /* Table-Only CSS Enhancement - Hindi gagalawin ang admin panel at back-to-home */

/* Main content area for tables */
.main-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
    min-height: calc(100vh - 200px);
}

/* Page header */
.page-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px 0;
}

.page-header h1 {
    color: #8B0000;
    font-size: 2.5rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.page-header p {
    color: #666;
    font-size: 1.1rem;
}

/* Success message */
.success-msg {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    animation: slideInDown 0.5s ease-out;
}

.success-msg i {
    font-size: 1.2rem;
}

/* Table container */
.table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 30px;
}

/* Search bar */
.search-bar {
    padding: 25px;
    background: linear-gradient(135deg, #8B0000, #a00);
    border-bottom: 1px solid #eee;
}

.search-bar input {
    width: 100%;
    padding: 15px 20px;
    border: none;
    border-radius: 50px;
    font-size: 16px;
    background: white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    outline: none;
}


.search-bar input:focus {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.search-bar input::placeholder {
    color: #999;
}

/* Table styles */
#userTable {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    background: white;
}

#userTable thead {
    background: linear-gradient(135deg, #8B0000, #a00);
    color: white;
}

#userTable thead th {
    padding: 20px 15px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    position: relative;
}

#userTable thead th:first-child {
    padding-left: 25px;
}

#userTable thead th:last-child {
    padding-right: 25px;
    text-align: center;
}

#userTable tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f0f0f0;
}

#userTable tbody tr:hover {
    background-color: #f8f9ff;
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

#userTable tbody tr:last-child {
    border-bottom: none;
}

#userTable tbody td {
    padding: 18px 15px;
    vertical-align: middle;
    border: none;
}

#userTable tbody td:first-child {
    padding-left: 25px;
    font-weight: 600;
    color: #8B0000;
}

#userTable tbody td:last-child {
    padding-right: 25px;
    text-align: center;
}

/* User role badges */
.user-role {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-admin {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    box-shadow: 0 2px 10px rgba(220, 53, 69, 0.3);
}

.role-user {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 2px 10px rgba(40, 167, 69, 0.3);
}

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
}

.edit-btn,
.delete-btn {
    padding: 8px 15px;
    border-radius: 25px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 2px solid transparent;
}

.edit-btn {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
}

.edit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
    background: linear-gradient(135deg, #0056b3, #004085);
}

.delete-btn {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
}

.delete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    background: linear-gradient(135deg, #c82333, #a71e2a);
}

/* Modal styles - para sa edit/delete forms */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    animation: fadeIn 0.3s ease-out;
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    border-radius: 15px;
    width: 90%;
    height: 80%;
    max-width: 600px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideInUp 0.4s ease-out;

    overflow-y: auto;  /* allow vertical scrolling */
    overflow-x: hidden; /* optional: prevent horizontal scrolling */
}


.modal-header {
    background: linear-gradient(135deg, #8B0000, #a00);
    color: white;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: rotate(90deg);
}

.modal-body {
    padding: 30px 25px;
}

/* Form styles sa modal */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #8B0000;
    background-color: white;
    box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
}

.form-group input[readonly] {
    background-color: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
}

.submit-btn {
    background: linear-gradient(135deg, #8B0000, #a00);
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 auto;
    box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(139, 0, 0, 0.4);
    background: linear-gradient(135deg, #a00, #8B0000);
}

/* Delete modal specific styles */
.modal-footer {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}

.confirm-btn,
.cancel-btn {
    padding: 12px 25px;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    border: none;
    font-size: 14px;
}

.confirm-btn {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
}

.confirm-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

.cancel-btn {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 3px 10px rgba(108, 117, 125, 0.3);
}

.cancel-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideInDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive design para sa table */
@media (max-width: 768px) {
    #userTable {
        font-size: 12px;
    }
    
    #userTable thead th,
    #userTable tbody td {
        padding: 10px 8px;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
    
    .edit-btn,
    .delete-btn {
        font-size: 11px;
        padding: 6px 12px;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}

@media (max-width: 480px) {
    .search-bar {
        padding: 15px;
    }
    
    .search-bar input {
        padding: 12px 15px;
        font-size: 14px;
    }
    
    /* Hide some columns sa mobile */
    #userTable thead th:nth-child(3),
    #userTable thead th:nth-child(4),
    #userTable tbody td:nth-child(3),
    #userTable tbody td:nth-child(4) {
        display: none;
    }
    
    .modal-body {
        padding: 20px 15px;
    }
}
        </style>
</head>
<body>
    <div class="admin-panel">
        <div class="category-tabs">
            <div class="admin-title">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </div>
            <a href="Manageusers.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'Manageusers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Manage Users
            </a>
<a href="gallery_admin.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : ''; ?>">
    <i class="fas fa-images"></i> Gallery
</a>
            <a href="orders.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Orders
            </a>
            <a href="MenuManagement.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'MenuManagement.php' ? 'active' : ''; ?>">
                <i class="fas fa-utensils"></i> Menu Management
            </a>
                    <a href="Returns.php" class="category-tab <?php echo basename($_SERVER['PHP_SELF']) == 'manage_returns.php' ? 'active' : ''; ?>">
            <i class="fas fa-undo"></i> Manage Returns
        </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="page-header">
            <h1>Manage Users</h1>
            <p>View, edit, and delete user accounts</p>
        </div>
        
        <?php if (isset($_GET['msg'])): ?>
        <div class="success-msg">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
        <?php endif; ?>
        
        <div class="table-container">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search users by name, email, or ID..." onkeyup="searchTable()">
            </div>
            
            <table id="userTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['userId']; ?></td>
                        <td><?php echo htmlspecialchars($row['firstName']); ?></td>
                        <td><?php echo htmlspecialchars($row['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['phoneNumber']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <span class="user-role <?php echo $row['role'] === 'Admin' ? 'role-admin' : 'role-user'; ?>">
                                <?php echo htmlspecialchars($row['role']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="#" class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="#" class="delete-btn" onclick="openDeleteModal(<?php echo $row['userId']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

                    <input type="hidden" name="userId" id="editUserId">
                    <input type="hidden" name="update" value="1">
                    
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" id="displayUserId" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstName" id="editFirstName" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastName" id="editLastName" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="editAddress" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phoneNumber" id="editPhoneNumber" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="editEmail" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="editRole">
                            <option value="User">User</option>
                            <option value="Admin">Admin</option>
                        </select>
                                            <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Deletion</h2>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this user?</p>
            <div class="modal-footer">
                <a href="#" id="confirmDeleteBtn" class="confirm-btn"><i class="fas fa-check"></i> Yes, Delete</a>
                <button onclick="closeDeleteModal()" class="cancel-btn"><i class="fas fa-times"></i> Cancel</button>
            </div>
        </div>
    </div>
</div>

    <a href="index.php" class="back-to-home">
        <i class="fas fa-home"></i> Back to Homepage
    </a>
</body>
</html>