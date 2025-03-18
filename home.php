<?php
include 'db.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}
$sql = "SELECT * FROM JobStatus";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-cell[data-status="Design"] {
            background-color: #f0ad4e;
        }

        .status-cell[data-status="Confirmation"] {
            background-color: #5bc0de;
        }

        .status-cell[data-status="Print"] {
            background-color: #0275d8;
        }

        .status-cell[data-status="Delivery"] {
            background-color: #5cb85c;
        }

        .status-cell[data-status="Finished"] {
            background-color: #d9534f;
        }

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
                    <th>Job Start Date</th>
                    <th>Deadline</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?= $row['status'] === 'Finished' ? 'finished-row' : '' ?>"
                        ondblclick="editJob(<?= $row['id'] ?>, '<?= $row['order_number'] ?>', '<?= $row['company_name'] ?>', '<?= $row['contact_number'] ?>', '<?= $row['job_start_date'] ?>', '<?= $row['deadline'] ?>', '<?= $row['status'] ?>')">
                        <td><?= $row['order_number'] ?></td>
                        <td><?= $row['company_name'] ?></td>
                        <td><?= $row['contact_number'] ?></td>
                        <td><?= $row['job_start_date'] ?></td>
                        <td><?= $row['deadline'] ?></td>
                        <td class="status-cell text-white text-center" data-status="<?= $row['status'] ?>">
                            <?= $row['status'] ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
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
                            <label for="editJobStartDate" class="form-label">Job Start Date</label>
                            <input type="datetime-local" class="form-control" id="editJobStartDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDeadline" class="form-label">Deadline</label>
                            <input type="datetime-local" class="form-control" id="editDeadline" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" required>
                                <option value="Design">Design</option>
                                <option value="Confirmation">Confirmation</option>
                                <option value="Print">Print</option>
                                <option value="Delivery">Delivery</option>
                                <option value="Finished">Finished</option>
                            </select>
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
        function editJob(id, order_number, company_name, contact_number, job_start_date, deadline, status) {
            console.log("Editing job with ID:", id);
            document.getElementById('jobId').value = id;
            document.getElementById('editOrderNumber').value = order_number;
            document.getElementById('editCompanyName').value = company_name;
            document.getElementById('editContactNumber').value = contact_number;
            document.getElementById('editJobStartDate').value = job_start_date.replace(' ', 'T');
            document.getElementById('editDeadline').value = deadline.replace(' ', 'T');
            document.getElementById('editStatus').value = status;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        function saveChanges() {
            const jobId = document.getElementById('jobId').value;
            const orderNumber = document.getElementById('editOrderNumber').value;
            const companyName = document.getElementById('editCompanyName').value;
            const contactNumber = document.getElementById('editContactNumber').value;
            const jobStartDate = document.getElementById('editJobStartDate').value.replace('T', ' ');
            const deadline = document.getElementById('editDeadline').value.replace('T', ' ');
            const status = document.getElementById('editStatus').value;

            const formData = new FormData();
            formData.append('jobId', jobId);
            formData.append('editOrderNumber', orderNumber);
            formData.append('editCompanyName', companyName);
            formData.append('editContactNumber', contactNumber);
            formData.append('editJobStartDate', jobStartDate);
            formData.append('editDeadline', deadline);
            formData.append('editStatus', status);

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

        document.getElementById('contact_number').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, ''); // Remove non-numeric characters
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10); // Restrict to 10 digits
            }
        });
    </script>
</body>

</html>