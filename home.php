<?php
include 'db.php';

// Configure 10-hour session settings (36000 seconds)
ini_set('session.gc_maxlifetime', 36000);
ini_set('session.cookie_lifetime', 36000);
session_set_cookie_params([
    'lifetime' => 36000,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

// Check for inactivity timeout (10 hours)
if (time() - $_SESSION['LAST_ACTIVITY'] > 36000) {
    session_unset();
    session_destroy();
    header('Location: login.php?reason=timeout');
    exit();
}

// Update last activity time stamp
$_SESSION['LAST_ACTIVITY'] = time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .finished-row {
            display: none;
        }

        #jobTable tbody tr {
            transition: background-color 0.3s ease;
        }

        #jobTable tbody tr:hover {
            background-color: rgb(103, 166, 233);
            color: #fff;
            cursor: pointer;
        }

        #jobTable tbody tr:focus-within {
            background-color: #5bc0de;
            color: #fff;
        }

        .status-dropdown {
            border: 1px solid #ccc;
            padding: 5px;
        }

        /* Dropdown colors based on selection */
        .status-dropdown option[value="NotPaid"] {
            background-color: #d889f7;
            color: black;
        }

        .status-dropdown option[value="Design"] {
            background-color: #f0ad4e;
            color: black;
        }

        .status-dropdown option[value="Confirmation"] {
            background-color: #5bc0de;
            color: black;
        }

        .status-dropdown option[value="Print"] {
            background-color: #09e9cb;
            color: white;
        }

        .status-dropdown option[value="Delivery"] {
            background-color: #5cb85c;
            color: white;
        }

        .status-dropdown option[value="Finished"] {
            background-color: #d9534f;
            color: white;
        }

        .status-dropdown option[value="Call"] {
            background-color: #a6d7ff;
            color: black;
        }

        .status-dropdown option[value="Chat"] {
            background-color: #ffb6c1;
            color: black;
        }

        #jobTable tbody td {
            font-weight: 100;
            font-family: 'Rubik', sans-serif;
        }

        .delete-btn {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .delete-btn:hover {
            transform: scale(1.2);
        }

        .action-cell {
            text-align: center;
        }

        #jobTable th:first-child,
        #jobTable td:first-child {
            width: 50px;
            text-align: center;
        }

        #statusFilter {
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
        }

        #statusFilter option {
            padding: 5px;
        }

        /* Color the options to match the status colors */
        #statusFilter option[value="Call"] {
            background-color: #a6d7ff;
        }

        #statusFilter option[value="Chat"] {
            background-color: #ffb6c1;
        }

        #statusFilter option[value="NotPaid"] {
            background-color: #d889f7;
        }

        #statusFilter option[value="Design"] {
            background-color: #f0ad4e;
        }

        #statusFilter option[value="Confirmation"] {
            background-color: #5bc0de;
        }

        #statusFilter option[value="Print"] {
            background-color: #09e9cb;
        }

        #statusFilter option[value="Delivery"] {
            background-color: #5cb85c;
        }

        #statusFilter option[value="Finished"] {
            background-color: #d9534f;
        }
    </style>
</head>

<body>
    <?php include('header.php'); ?>
    <div class="container-fluid">
        <h1 class="mb-4">Job Status List</h1>
        <div class="mb-4">
            <div class="row">
            <div class="col-md-10">
                    <input type="text" id="searchBar" class="form-control"
                        placeholder="Search by Order Number or Company Name" onkeyup="searchJobs()">
                </div>
                <div class="col-md-2">
                    <select id="statusFilter" class="form-select" onchange="filterByStatus()">
                        <option value="">All Statuses</option>
                        <option value="Call">Call</option>
                        <option value="Chat">Chat</option>
                        <option value="NotPaid">Not Paid</option>
                        <option value="Design">Design</option>
                        <option value="Confirmation">Confirmation</option>
                        <option value="Print">Print</option>
                        <option value="Delivery">Delivery</option>
                        <option value="Finished">Finished</option>
                    </select>
                </div>
                
            </div>
        </div>
        <table class="table table-bordered" id="jobTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Order Number</th>
                    <th>Company Name</th>
                    <th>Contact Number</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Job Start Date</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM job_status WHERE hidden = 0";
                $result = $conn->query($sql);
                $rowCount = 1;

                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?= $row['status'] === 'Finished' ? 'finished-row' : '' ?>"
                        ondblclick="editJob(<?= $row['id'] ?>, '<?= htmlspecialchars($row['order_number']) ?>', '<?= htmlspecialchars($row['company_name']) ?>', '<?= htmlspecialchars($row['contact_number']) ?>', '<?= htmlspecialchars($row['category']) ?>', <?= $row['quantity'] ?>, '<?= $row['job_start_date'] ?>', '<?= $row['deadline'] ?>', '<?= $row['status'] ?>')">
                        <td class="text-center">
                            <?= ($row['status'] !== 'Finished') ? $rowCount++ : '-' ?>
                        </td>
                        <td><?= htmlspecialchars($row['order_number']) ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['contact_number']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['job_start_date'] ?></td>
                        <td><?= $row['deadline'] ?></td>
                        <td class="status-cell text-white text-center">
                            <select class="form-select status-dropdown" data-id="<?= $row['id'] ?>">
                                <option value="Call" <?= ($row['status'] == 'Call') ? 'selected' : '' ?>>Call</option>
                                <option value="Chat" <?= ($row['status'] == 'Chat') ? 'selected' : '' ?>>Chat</option>
                                <option value="NotPaid" <?= ($row['status'] == 'NotPaid') ? 'selected' : '' ?>>Not Paid
                                </option>
                                <option value="Design" <?= ($row['status'] == 'Design') ? 'selected' : '' ?>>Design</option>
                                <option value="Confirmation" <?= ($row['status'] == 'Confirmation') ? 'selected' : '' ?>>
                                    Confirmation</option>
                                <option value="Print" <?= ($row['status'] == 'Print') ? 'selected' : '' ?>>Print</option>
                                <option value="Delivery" <?= ($row['status'] == 'Delivery') ? 'selected' : '' ?>>Delivery
                                </option>
                                <option value="Finished" <?= ($row['status'] == 'Finished') ? 'selected' : '' ?>>Finished
                                </option>
                            </select>
                        </td>
                        <td class="action-cell">
                            <button class="delete-btn"
                                onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['order_number']) ?>')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>

        </table>
    </div>

    <!-- Modal for editing job -->
    <div class="modal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editJobForm">
                        <input type="hidden" id="jobId">
                        <div class="mb-3">
                            <label for="editOrderNumber" class="form-label">Order Number</label>
                            <input type="text" class="form-control" id="editOrderNumber" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCompanyName" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="editCompanyName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="editContactNumber" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <input type="text" class="form-control" id="editCategory" required>
                        </div>
                        <div class="mb-3">
                            <label for="editQuantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="editQuantity" required>
                        </div>
                        <div class="mb-3">
                            <label for="editJobStartDate" class="form-label">Job Start Date</label>
                            <input type="datetime-local" class="form-control" id="editJobStartDate" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editDeadline" class="form-label">Deadline</label>
                            <input type="datetime-local" class="form-control" id="editDeadline" required>
                        </div>

                        <button type="button" class="btn btn-primary" onclick="saveChanges()">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete order <span id="orderNumberToDelete"></span>? This action cannot
                        be undone.</p>
                    <div class="mb-3">
                        <label for="deletePassword" class="form-label">Enter your password to confirm:</label>
                        <input type="password" class="form-control" id="deletePassword" required>
                        <div id="passwordError" class="text-danger mt-2" style="display: none;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let jobIdToDelete = null;

        function confirmDelete(id, orderNumber) {
            jobIdToDelete = id;
            document.getElementById('orderNumberToDelete').textContent = orderNumber;
            document.getElementById('deletePassword').value = '';
            document.getElementById('passwordError').style.display = 'none';

            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();

            // Stop event propagation to prevent triggering the row's double-click event
            event.stopPropagation();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            const password = document.getElementById('deletePassword').value;
            const errorElement = document.getElementById('passwordError');

            if (!password) {
                errorElement.textContent = 'Please Enter Your Password';
                errorElement.style.display = 'block';
                return;
            }

            if (jobIdToDelete) {
                deleteJob(jobIdToDelete, password);
            }
        });

        function deleteJob(id, password) {
            fetch('delete_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&password=${encodeURIComponent(password)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                        modal.hide();

                        // Refresh the entire table (reload the page)
                        location.reload(); // ← This will refresh the table

                        // Optional: Show a success message
                        alert('Job deleted successfully!');
                    } else {
                        const errorElement = document.getElementById('passwordError');
                        errorElement.textContent = data.message || 'Failed to delete job';
                        errorElement.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the job.');
                });
        }

        function editJob(id, order_number, company_name, contact_number, category, quantity, job_start_date, deadline, status) {
            document.getElementById('jobId').value = id;
            document.getElementById('editOrderNumber').value = order_number;
            document.getElementById('editCompanyName').value = company_name;
            document.getElementById('editContactNumber').value = contact_number;
            document.getElementById('editCategory').value = category;
            document.getElementById('editQuantity').value = quantity;
            document.getElementById('editJobStartDate').value = job_start_date.replace(' ', 'T');
            document.getElementById('editDeadline').value = deadline.replace(' ', 'T');

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function saveChanges() {
            const jobId = document.getElementById('jobId').value;
            const orderNumber = document.getElementById('editOrderNumber').value;
            const companyName = document.getElementById('editCompanyName').value;
            const contactNumber = document.getElementById('editContactNumber').value;
            const category = document.getElementById('editCategory').value;
            const quantity = document.getElementById('editQuantity').value;
            const jobStartDate = document.getElementById('editJobStartDate').value.replace('T', ' ');
            const deadline = document.getElementById('editDeadline').value.replace('T', ' ');


            const formData = new FormData();
            formData.append('jobId', jobId);
            formData.append('editOrderNumber', orderNumber);
            formData.append('editCompanyName', companyName);
            formData.append('editContactNumber', contactNumber);
            formData.append('editCategory', category);
            formData.append('editQuantity', quantity);
            formData.append('editJobStartDate', jobStartDate);
            formData.append('editDeadline', deadline);


            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_job.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        alert('Job updated successfully!');
                        window.location.reload();
                    } else {
                        alert('Failed to update job: ' + response.message);
                    }
                } else {
                    alert('Server error: Unable to update job.');
                }
            };

            xhr.send(formData);
        }

        function searchJobs() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const rows = document.querySelectorAll('#jobTable tbody tr');

            rows.forEach(row => {
                const orderNumber = row.cells[0].textContent.toLowerCase();
                const companyName = row.cells[1].textContent.toLowerCase();

                if (orderNumber.includes(searchTerm) || companyName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".status-dropdown").forEach(dropdown => {
                setDropdownColor(dropdown);

                dropdown.addEventListener("change", function () {
                    const jobId = this.getAttribute("data-id");
                    const newStatus = this.value;
                    const row = this.closest('tr');

                    // Update status in database
                    fetch("update_status.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `id=${jobId}&status=${newStatus}`
                    })
                        .then(response => response.text())
                        .then(data => {
                            if (data === "success") {
                                setDropdownColor(this);

                                if (newStatus === 'Finished') {
                                    // If status changed to Finished, reload the page
                                    alert("Status updated to Finished. Page will refresh.");
                                    location.reload();
                                } else {
                                    // For other status changes, just update the color
                                    alert("Status updated successfully!");
                                }
                            } else {
                                alert("Error updating status.");
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            alert("Error updating status.");
                        });
                });
            });

            // Set color for status filter
            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) {
                setDropdownColor(statusFilter);
                statusFilter.addEventListener('change', function () {
                    setDropdownColor(this);
                });
            }
        });




        function setDropdownColor(dropdown) {
            let statusColor = {
                "Call": "#a6d7ff",
                "Chat": "#ffb6c1",
                "NotPaid": "#d889f7",
                "Design": "#f0ad4e",
                "Confirmation": "#5bc0de",
                "Print": "#09e9cb",
                "Delivery": "#5cb85c",
                "Finished": "#d9534f"
            };

            if (dropdown.classList.contains('status-dropdown')) {
                dropdown.style.backgroundColor = statusColor[dropdown.value] || "white";
                dropdown.style.color = "white";
            } else if (dropdown.id === 'statusFilter') {
                dropdown.style.backgroundColor = statusColor[dropdown.value] || "#f8f9fa";
            }
        }

        function filterByStatus() {
            const selectedStatus = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#jobTable tbody tr');

            rows.forEach(row => {
                const statusDropdown = row.querySelector('.status-dropdown');
                const rowStatus = statusDropdown ? statusDropdown.value : '';

                if (!selectedStatus || rowStatus === selectedStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function searchJobs() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const selectedStatus = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#jobTable tbody tr');

            rows.forEach(row => {
                const orderNumber = row.cells[1].textContent.toLowerCase(); // Changed index from 0 to 1
                const companyName = row.cells[2].textContent.toLowerCase(); // Changed index from 1 to 2
                const statusDropdown = row.querySelector('.status-dropdown');
                const rowStatus = statusDropdown ? statusDropdown.value : '';

                const matchesSearch = orderNumber.includes(searchTerm) || companyName.includes(searchTerm);
                const matchesStatus = !selectedStatus || rowStatus === selectedStatus;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>