<?php
include 'db.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

// Order by date in descending order (latest jobs appear first)
$sql = "SELECT * FROM JobStatus ORDER BY date DESC"; 
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">Job Status History</h1>
        <div class="mb-4">
            <input type="text" id="searchBar" class="form-control" placeholder="Search by Order Number or Company Name" onkeyup="searchJobs()">
        </div>

        <table class="table table-bordered table-striped" id="jobTable">
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
                    <tr ondblclick="editJob(<?= $row['id'] ?>, '<?= $row['order_number'] ?>', '<?= $row['company_name'] ?>', '<?= $row['job_start_date'] ?>', '<?= $row['deadline'] ?>', '<?= $row['status'] ?>')">
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

    </style>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
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
</body>

</html>
