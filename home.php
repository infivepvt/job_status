<?php
include 'db.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500&display=swap" rel="stylesheet">

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
        .status-dropdown option[value="NotYet"] {
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

        #jobTable tbody td {
            font-weight: 100;
            font-family: 'Rubik', sans-serif;
        }
    </style>
</head>

<body>
    <?php include('header.php'); ?>
    <div class="container mt-5">
        <h1 class="mb-4">Job Status List</h1>
        <div class="mb-4">
            <input type="text" id="searchBar" class="form-control" placeholder="Search by Order Number or Company Name"
                onkeyup="searchJobs()">
        </div>
        <table class="table table-bordered " id="jobTable">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Company Name</th>
                    <th>Contact Number</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Job Start Date</th>
                    <th>Deadline</th>
                    <th>Status</th>

                </tr>
            </thead>
            <tbody>
                <?php $sql = "SELECT * FROM job_status";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?= $row['status'] === 'Finished' ? 'finished-row' : '' ?>"
                        ondblclick="editJob(<?= $row['id'] ?>, '<?= $row['order_number'] ?>', '<?= $row['company_name'] ?>', '<?= $row['contact_number'] ?>', '<?= $row['category'] ?>', <?= $row['quantity'] ?>, '<?= $row['job_start_date'] ?>', '<?= $row['deadline'] ?>', '<?= $row['status'] ?>')">
                        <td><?= $row['order_number'] ?></td>
                        <td><?= $row['company_name'] ?></td>
                        <td><?= $row['contact_number'] ?></td>
                        <td><?= $row['category'] ?></td> <!-- Display Category -->
                        <td><?= $row['quantity'] ?></td> <!-- Display Quantity -->
                        <td><?= $row['job_start_date'] ?></td>
                        <td><?= $row['deadline'] ?></td>
                        <td class="status-cell text-white text-center">
                            <select class="form-select status-dropdown" data-id="<?= $row['id'] ?>">
                                <option value="NotYet" <?= ($row['status'] == 'NotYet') ? 'selected' : '' ?>>Not Yet</option>
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
                            <input type="text" class="form-control" id="editOrderNumber" required readonly>
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editJob(id, order_number, company_name, contact_number, category, quantity, job_start_date, deadline, status) {
            document.getElementById('jobId').value = id;
            document.getElementById('editOrderNumber').value = order_number;
            document.getElementById('editCompanyName').value = company_name;
            document.getElementById('editContactNumber').value = contact_number;
            document.getElementById('editCategory').value = category; // Populate Category
            document.getElementById('editQuantity').value = quantity; // Populate Quantity
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
            // Hide finished rows when the page loads
            hideFinishedRows();

            document.querySelectorAll(".status-dropdown").forEach(dropdown => {
                setDropdownColor(dropdown);

                dropdown.addEventListener("change", function () {
                    const jobId = this.getAttribute("data-id");
                    const newStatus = this.value;

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
                                alert("Status updated successfully!");
                                setDropdownColor(this);
                                toggleRowVisibility(newStatus, this.closest('tr')); // Toggle row visibility
                            } else {
                                alert("Error updating status.");
                            }
                        });
                });
            });

            // Hide rows with "Finished" status
            function hideFinishedRows() {
                const rows = document.querySelectorAll('#jobTable tbody tr');
                rows.forEach(row => {
                    const statusCell = row.querySelector('td.status-cell select');
                    if (statusCell && statusCell.value === 'Finished') {
                        row.style.display = 'none'; // Hide rows with "Finished" status
                    }
                });
            }

            // Show or hide the row based on its status
            function toggleRowVisibility(status, row) {
                if (status === 'Finished') {
                    row.style.display = 'none'; // Hide the row if status is "Finished"
                } else {
                    row.style.display = ''; // Show the row if status is not "Finished"
                }
            }
        });

        // Set the background color based on the status
        function setDropdownColor(dropdown) {
            let statusColor = {
                "Design": "#f0ad4e",
                "Confirmation": "#5bc0de",
                "Print": "#09e9cb",
                "Delivery": "#5cb85c",
                "Finished": "#d9534f",
                "NotYet": "#d889f7"
            };

            dropdown.style.backgroundColor = statusColor[dropdown.value] || "white";
            dropdown.style.color = "white"; // Ensure text is visible
        }


    </script>
</body>

</html>